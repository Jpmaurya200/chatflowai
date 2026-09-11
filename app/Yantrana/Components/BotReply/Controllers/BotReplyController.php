<?php
/**
* BotReplyController.php - Controller file
*
* This file is part of the BotReply component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\BotReply\Controllers;

use Illuminate\Validation\Rule;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Base\BaseController;

use Illuminate\Database\Query\Builder;
use App\Yantrana\Support\CommonPostRequest;
use App\Yantrana\Components\BotReply\BotReplyEngine;
use App\Yantrana\Components\BotReply\Repositories\BotReplyRepository;
use App\Yantrana\Components\Contact\Repositories\ContactCustomFieldRepository;
use App\Yantrana\Components\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;

class BotReplyController extends BaseController
{
    /**
     * @var  BotReplyEngine $botReplyEngine - BotReply Engine
     */
    protected $botReplyEngine;

    /**
     * @var  UserRepository $userRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var  BotReplyRepository $botReplyRepository - BotReply Repository
     */
    protected $botReplyRepository;

    /**
     * @var  ContactCustomFieldRepository $contactCustomFieldRepository - ContactCustomField Repository
     */
    protected $contactCustomFieldRepository;

    /**
      * Constructor
      *
      * @param  BotReplyEngine $botReplyEngine - BotReply Engine
      * @param  UserRepository $userRepository - User Repository
      * @param  BotReplyRepository $botReplyRepository - BotReply Repository
      * @param  ContactCustomFieldRepository $contactCustomFieldRepository - ContactCustomField Repository
      *
      * @return  void
      *-----------------------------------------------------------------------*/
    public function __construct(
        BotReplyEngine $botReplyEngine,
        UserRepository $userRepository,
        BotReplyRepository $botReplyRepository,
        ContactCustomFieldRepository $contactCustomFieldRepository
    ) {
        $this->botReplyEngine = $botReplyEngine;
        $this->userRepository = $userRepository;
        $this->botReplyRepository = $botReplyRepository;
        $this->contactCustomFieldRepository = $contactCustomFieldRepository;
    }


    /**
      * list of BotReply
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function showBotReplyView()
    {
        validateVendorAccess('manage_bot_replies');
        // load the view
        $preData = $this->botReplyEngine->preDataForBots();

        // Get vendor team members for team assignment nodes
        $vendorId = getVendorId();
        $vendorTeamMembers = $this->userRepository->getVendorMessagingUsers($vendorId);

        // Debug logging to see what users are available
        Log::info('Vendor team members for dropdown', [
            'vendor_id' => $vendorId,
            'team_members_count' => $vendorTeamMembers->count(),
            'team_members' => $vendorTeamMembers->map(function($user) {
                return [
                    '_uid' => $user->_uid,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role_id' => $user->user_roles__id,
                ];
            })->toArray()
        ]);

        return $this->loadView('bot-reply.list', [
            'dynamicFields' => $preData->data('dynamicFields'),
            'contactCustomFields' => $preData->data('contactCustomFields'),
            'whatsAppTemplates' => $preData->data('whatsAppTemplates'),
            'vendorTeamMembers' => $vendorTeamMembers
        ]);
    }
    /**
      * list of BotReply
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareBotReplyList()
    {
        validateVendorAccess('manage_bot_replies');
        // respond with dataTables preparations
        return $this->botReplyEngine->prepareBotReplyDataTableSource();
    }

    /**
        * BotReply process delete
        *
        * @param  mix $botReplyIdOrUid
        *
        * @return  json object
        *---------------------------------------------------------------- */

    public function processBotReplyDelete(BaseRequest $request, $botReplyIdOrUid)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        // ask engine to process the request
        $processReaction = $this->botReplyEngine->processBotReplyDelete($botReplyIdOrUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
        * BotReply duplicate
        *
        * @param  mix $botReplyIdOrUid
        *
        * @return  json object
        *---------------------------------------------------------------- */

    public function processBotReplyDuplicate(BaseRequest $request, $botReplyIdOrUid)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        
        // Get context flow UID from request (if provided from flow builder)
        $contextFlowUid = $request->input('context_flow_uid');

        // ask engine to process the request
        $processReaction = $this->botReplyEngine->processBotReplyDuplicate($botReplyIdOrUid, $contextFlowUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
      * BotReply create process
      *
      * @param  object BaseRequest $request
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function processBotReplyCreate(BaseRequest $request)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $triggerTypeValidations = [];
        $vendorId = getVendorId();
        // if bot flow
        if(!$request->bot_flow_uid) {
            if($request->trigger_type != 'welcome') {
                $triggerTypeValidations = [
                    "required",
                    "max:250",
                ];
            }
        }
        // if bot flow item
        if($request->bot_flow_uid) {
            $request->merge([
                'trigger_type' => 'is',
                'reply_trigger' => null,
            ]);
        }
        $validations = [
            "trigger_type" => "required",
            "reply_trigger" => $triggerTypeValidations,
        ];
        // if not bot flow item
        if(!$request->bot_flow_uid) {
            $validations['name'] = [
                "required",
                "max:200",
                Rule::unique('bot_replies')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId)->whereNull('bot_flows__id'))
            ];
        }
        if(in_array($request->message_type, [
            'simple',
            'interactive',
            'question',
        ])) {
            $validations['reply_text'] = "required";
        }
        // For node types that map their own text fields to reply_text in the engine,
        // we validate their specific required fields instead of reply_text
        if($request->message_type == 'wait') {
            // wait_message is optional, but if provided should be reasonable length
            $validations['wait_message'] = 'nullable|string|max:500';
            $validations['wait_delay_seconds'] = 'required|integer|min:1|max:3600';
        }
        if($request->message_type == 'team_assignment') {
            $validations['assigned_team_member'] = 'required';
            $validations['assignment_message'] = 'nullable|string|max:500';
        }
        if($request->message_type == 'webhook') {
            $validations['webhook_url'] = 'required|url';
            $validations['http_method'] = 'required|in:GET,POST,PUT,PATCH,DELETE';
            $validations['success_message'] = 'nullable|string|max:500';
            $validations['error_message'] = 'nullable|string|max:500';
            $validations['timeout'] = 'nullable|integer|min:5|max:120';
        }
        if($request->message_type == 'custom_field') {
            $validations['custom_field_id'] = 'required';
            $validations['question_text'] = 'required|string|max:500';
        }
        if($request->message_type == 'whatsapp_template') {
            $validations['whatsapp_template_id'] = 'required';
        }
        // Stay in session nodes don't require reply_text
        if($request->message_type == 'stay_in_session') {
            // No reply_text validation needed for stay_in_session nodes
        }
        if($request->message_type == 'goto') {
            $validations['goto_target_node'] = "required";
        }
        if($request->message_type == 'question') {
            $validations['question_store_field'] = 'required|string|max:50';
        }
        if(in_array($request->message_type, [
            'media',
        ])) {
            $validations['media_header_type'] = [
                "required",
                Rule::in([
                    'image',
                    'video',
                    'audio',
                    'document',
                ])
            ];
            $validations['uploaded_media_file_name'] = "required";
        }
        // interaction message thats button etc
        if($request->message_type == 'interactive') {
            $validations['header_type'] = [
                Rule::in([
                    '',
                    'text',
                    'image',
                    'video',
                    'document',
                ])
            ];
            if(in_array($request->header_type, [
                '',
                'text',
            ])) {
                $validations['interactive_type'] = [
                    'required',
                    Rule::in([
                        'button',
                        'cta_url',
                        'list',
                    ])
                ];
            } else {
                $validations['interactive_type'] = [
                    'required',
                    Rule::in([
                        'button',
                        'cta_url',
                    ])
                ];
            }

            if($request->interactive_type == 'cta_url') {
                $validations['button_display_text'] = "required|min:1|max:20";
                $validations['button_url'] = "required";
                // list type
            } elseif($request->interactive_type == 'list') {
                $validations['list_button_text'] = "required|min:1|max:20";
                $validations['sections'] = ["required","array","min:1","max:10"];
                if(!empty($request->sections)) {
                    foreach ($request->sections as $sectionKey => $section) {
                        $validations["sections.$sectionKey.rows"] = ["required","array","min:1","max:10"];
                        if(!empty($section['rows'])) {
                            $collectedRowIds = [];
                            foreach ($section['rows'] as $sectionRowKey => $sectionRow) {
                                $collectedRowIds[] = $sectionRow['row_id'];
                                $validations["sections.$sectionKey.rows.$sectionRowKey.row_id"] = ["required","min:1","max:200", 'alpha_dash'];
                                $validations["sections.$sectionKey.rows.$sectionRowKey.title"] = ["required","min:1","max:24"];
                                $validations["sections.$sectionKey.rows.$sectionRowKey.description"] = ["nullable","max:72"];
                            }
                            if(array_filter($collectedRowIds) != array_unique(array_filter($collectedRowIds))) {
                                return $this->processResponse(3, [
                                    3 => __tr('Row IDs in Section should be unique.')
                                ], [], true);
                            }
                        }
                    }
                }
            } else {
                // must be reply button type
                // at least 1 button is required
                $validations['buttons.1'] = "required|min:1|max:20";
                $validations['buttons.2'] = "nullable|min:1|max:20";
                $validations['buttons.3'] = "nullable|min:1|max:20";
                if(array_filter($request->buttons ?: []) != array_unique(array_filter($request->buttons ?: []))) {
                    return $this->processResponse(3, [
                        3 => __tr('Buttons labels should be unique.')
                    ], [], true);
                }
            }

            // if header is not a text then it should be media
            if(($request->header_type != 'text') and ($request->message_type != 'interactive')) {
                $validations['uploaded_media_file_name'] = "required";
            } elseif($request->message_type != 'interactive') {
                // if header text then its required
                $validations['header_text'] = "required";
            }
        }
        // process the validation based on the provided rules
        $request->validate($validations, [
            'uploaded_media_file_name' => __tr('Media is required')
        ]);
        // ask engine to process the request
        $processReaction = $this->botReplyEngine->processBotReplyCreate($request);
        // get back with response
        return $this->processResponse($processReaction);
    }

    /**
      * BotReply get update data
      *
      * @param  mix $botReplyIdOrUid
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function updateBotReplyData($botReplyIdOrUid)
    {
        validateVendorAccess('manage_bot_replies');
        // ask engine to process the request
        $processReaction = $this->botReplyEngine->prepareBotReplyUpdateData($botReplyIdOrUid);

        if ($processReaction->success()) {
            // Get vendor team members for team assignment nodes
            $vendorId = getVendorId();
            $vendorTeamMembers = $this->userRepository->getVendorMessagingUsers($vendorId);

            // Get contact custom fields for question and custom field nodes
            $contactCustomFields = $this->contactCustomFieldRepository->fetchItAll([
                'vendors__id' => $vendorId
            ]);

            // Get flow bots for goto nodes (if this is part of a flow)
            $flowBots = [];
            $botReplyData = $processReaction->data();
            if (!empty($botReplyData['bot_flows__id'])) {
                $flowBots = $this->botReplyRepository->fetchItAll([
                    'bot_flows__id' => $botReplyData['bot_flows__id'],
                    'vendors__id' => $vendorId,
                ]);
            }

            // Add all necessary data to the response
            $processReaction->updateData('vendorTeamMembers', $vendorTeamMembers->toArray());
            $processReaction->updateData('contactCustomFields', $contactCustomFields->toArray());
            $processReaction->updateData('flowBots', is_array($flowBots) ? $flowBots : $flowBots->toArray());
        }

        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
      * BotReply process update
      *
      * @param  mix @param  mix $botReplyIdOrUid
      * @param  object BaseRequest $request
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function processBotReplyUpdate(BaseRequest $request)
    {
        validateVendorAccess('manage_bot_replies');
        $triggerTypeValidations = [];
        if(!$request->bot_flow_uid) {
            if($request->trigger_type != 'welcome') {
                $triggerTypeValidations = [
                    "required",
                    "max:250",
                ];
            }
        }
        $validations = [
            'botReplyIdOrUid' => 'required',
            "name" => [
                "required",
                "max:200",
            ],
            // "reply_text" => "required",
            // "trigger_type" => "required",
            "reply_trigger" => $triggerTypeValidations,
        ];
        if($request->bot_flow_uid) {
            $request->merge([
                'trigger_type' => 'is',
                // 'reply_trigger' => null,
            ]);
        }
        if(!$request->bot_flow_uid) {
            $validations['trigger_type'] = [
                'required'
            ];
        }
        if(in_array($request->message_type, [
            'simple',
            'interactive',
            'question',
        ])) {
            $validations['reply_text'] = "required";
        }
        // For node types that map their own text fields to reply_text in the engine,
        // we validate their specific required fields instead of reply_text
        if($request->message_type == 'wait') {
            // wait_message is optional, but if provided should be reasonable length
            $validations['wait_message'] = 'nullable|string|max:500';
            $validations['wait_delay_seconds'] = 'required|integer|min:1|max:3600';
        }
        if($request->message_type == 'team_assignment') {
            $validations['assigned_team_member'] = 'required';
            $validations['assignment_message'] = 'nullable|string|max:500';
        }
        if($request->message_type == 'webhook') {
            $validations['webhook_url'] = 'required|url';
            $validations['http_method'] = 'required|in:GET,POST,PUT,PATCH,DELETE';
            $validations['success_message'] = 'nullable|string|max:500';
            $validations['error_message'] = 'nullable|string|max:500';
            $validations['timeout'] = 'nullable|integer|min:5|max:120';
        }
        if($request->message_type == 'custom_field') {
            $validations['custom_field_id'] = 'required';
            $validations['question_text'] = 'required|string|max:500';
        }
        if($request->message_type == 'whatsapp_template') {
            $validations['whatsapp_template_id'] = 'required';
        }
        // Stay in session nodes don't require reply_text
        if($request->message_type == 'stay_in_session') {
            // No reply_text validation needed for stay_in_session nodes
        }
        if($request->message_type == 'goto') {
            $validations['goto_target_node'] = "required";
        }
        if($request->message_type == 'question') {
            $validations['question_store_field'] = 'required|string|max:50';
        }
        if(in_array($request->message_type, [
            'media',
        ])) {
            $validations['media_header_type'] = [
                "required",
                Rule::in([
                    'image',
                    'video',
                    'audio',
                    'document',
                ])
            ];
        }

        if($request->message_type == 'interactive') {
            if($request->interactive_type == 'cta_url') {
                $validations['button_display_text'] = "required|min:1|max:20";
                $validations['button_url'] = "required";
            } elseif($request->interactive_type == 'list') {
                $validations['list_button_text'] = "required|min:1|max:20";
                $validations['sections'] = ["required","array","min:1","max:10"];
                if(!empty($request->sections)) {
                    foreach ($request->sections as $sectionKey => $section) {
                        $validations["sections.$sectionKey.rows"] = ["required","array","min:1","max:10"];
                        if(!empty($section['rows'])) {
                            $collectedRowIds = [];
                            foreach ($section['rows'] as $sectionRowKey => $sectionRow) {
                                $collectedRowIds[] = $sectionRow['row_id'];
                                $validations["sections.$sectionKey.rows.$sectionRowKey.row_id"] = ["required","min:1","max:200",'alpha_dash'];
                                $validations["sections.$sectionKey.rows.$sectionRowKey.title"] = ["required","min:1","max:24"];
                                $validations["sections.$sectionKey.rows.$sectionRowKey.description"] = ["nullable","max:72"];
                            }
                            if(array_filter($collectedRowIds) != array_unique(array_filter($collectedRowIds))) {
                                return $this->processResponse(3, [
                                    3 => __tr('Row IDs in Section should be unique.')
                                ], [], true);
                            }
                        }
                    }
                }
            } else {
                // must be reply button type
                // at least 1 button is required
                $validations['buttons.1'] = "required|min:1|max:20";
                $validations['buttons.2'] = "nullable|min:1|max:20";
                $validations['buttons.3'] = "nullable|min:1|max:20";
                if(array_filter($request->buttons) != array_unique(array_filter($request->buttons))) {
                    return $this->processResponse(3, [
                        3 => __tr('Buttons labels should be unique.')
                    ], [], true);
                }
            }
            // if header is not a text then it should be media
            if($request->header_type == 'text') {
                // if header text then its required
                $validations['header_text'] = "required";
            }
        }
        // process the validation based on the provided rules
        $request->validate($validations);
        // ask engine to process the request
        $processReaction = $this->botReplyEngine->processBotReplyUpdate($request->get('botReplyIdOrUid'), $request);
        // get back with response
        return $this->processResponse($processReaction, [], [], true);
    }
}