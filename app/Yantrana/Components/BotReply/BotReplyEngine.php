<?php
/**
* BotReplyEngine.php - Main component file
*
* This file is part of the BotReply component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\BotReply;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Yantrana\Base\BaseEngine;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\Media\MediaEngine;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;
use App\Yantrana\Components\BotReply\Repositories\BotFlowRepository;
use App\Yantrana\Components\BotReply\Repositories\BotReplyRepository;
use App\Yantrana\Components\BotReply\Interfaces\BotReplyEngineInterface;
use App\Yantrana\Components\Contact\Repositories\ContactCustomFieldRepository;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;
use App\Yantrana\Components\Flows\Services\WhatsAppFlowService;

class BotReplyEngine extends BaseEngine implements BotReplyEngineInterface
{
    /**
     * @var  BotReplyRepository $botReplyRepository - BotReply Repository
     */
    protected $botReplyRepository;

    /**
     * @var  ContactCustomFieldRepository $contactCustomFieldRepository - ContactCustomField Repository
     */
    protected $contactCustomFieldRepository;

    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var WhatsAppServiceEngine - WhatsApp Service Engine
     */
    protected $whatsAppServiceEngine;

    /**
     * @var  BotFlowRepository $botFlowRepository - BotFlow Repository
     */
    protected $botFlowRepository;

    /**
     * @var  UserRepository $userRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var  WhatsAppTemplateRepository $whatsAppTemplateRepository - WhatsApp Template Repository
     */
    protected $whatsAppTemplateRepository;

    /**
      * Constructor
      *
      * @param  BotReplyRepository $botReplyRepository - BotReply Repository
      * @param  ContactCustomFieldRepository $contactCustomFieldRepository - BotReply Repository
      * @param  MediaEngine $mediaEngine
      * @param  WhatsAppServiceEngine $whatsAppServiceEngine
      * @param  BotFlowRepository $botFlowRepository
      * @param  UserRepository $userRepository
      * @param  WhatsAppTemplateRepository $whatsAppTemplateRepository - WhatsApp Template Repository
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    public function __construct(
        BotReplyRepository $botReplyRepository,
        ContactCustomFieldRepository $contactCustomFieldRepository,
        MediaEngine $mediaEngine,
        WhatsAppServiceEngine $whatsAppServiceEngine,
        BotFlowRepository $botFlowRepository,
        UserRepository $userRepository,
        WhatsAppTemplateRepository $whatsAppTemplateRepository,
    ) {
        $this->botReplyRepository = $botReplyRepository;
        $this->contactCustomFieldRepository = $contactCustomFieldRepository;
        $this->mediaEngine = $mediaEngine;
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
        $this->botFlowRepository = $botFlowRepository;
        $this->userRepository = $userRepository;
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
    }

    /**
     * Get contact dynamic fields and custom fields
     *
     * @return EngineResponse
     */
    public function preDataForBots()
    {
        $vendorId = getVendorId();

        $dynamicFieldsToReplace = [
            '{first_name}',
            '{last_name}',
            '{full_name}',
            '{phone_number}',
            '{email}',
            '{country}',
            '{language_code}',
        ];

        $customFields = $this->contactCustomFieldRepository->fetchItAll([
            'vendors__id' => $vendorId
        ]);

        foreach ($customFields as $customField) {
            $dynamicFieldsToReplace[] = "{{$customField->input_name}}";
        }

        // Get WhatsApp templates for the vendor
        $whatsAppTemplates = $this->whatsAppTemplateRepository->getApprovedTemplatesByNewest();

        return $this->engineSuccessResponse([
            'dynamicFields' => $dynamicFieldsToReplace,
            'contactCustomFields' => $customFields, // Pass custom fields to view
            'whatsAppTemplates' => $whatsAppTemplates // Pass WhatsApp templates to view
        ]);
    }

    /**
      * BotReply datatable source
      *
      * @return  array
      *---------------------------------------------------------------- */
    public function prepareBotReplyDataTableSource()
    {
        $botReplyCollection = $this->botReplyRepository->fetchBotReplyDataTableSource();
        $orderStatuses = configItem('status_codes');
        $botTriggerTypes = configItem('bot_reply_trigger_types');
        // required columns for DataTables
        $requireColumns = [
            '_id',
            '_uid',
            'name',
            'reply_text',
            'trigger_type',
            'trigger_type' => function ($rowData) use(&$botTriggerTypes) {
                return $botTriggerTypes[$rowData['trigger_type']]['title'] ?? '';
            },
            'reply_trigger' => function ($rowData) {
                return ($rowData['trigger_type'] != 'welcome') ? $rowData['reply_trigger'] : '';
            },
            'created_at' => function ($rowData) {
                return formatDateTime($rowData['created_at']);
            },
            'status' => function ($key) use (&$orderStatuses) {
                if($key['status'] === null) {
                    $key['status'] = 1; // active
                }
                return $key['status'];
            },
            'bot_type' => function ($rowData) {
                $botReplyType = __tr('Simple');
                if($rowData['__data']['media_message'] ?? null) {
                    $botReplyType = __tr('Media');
                } elseif($rowData['__data']['interaction_message'] ?? null) {
                    $botReplyType = __tr('Interactive/Buttons');
                } elseif($rowData['__data']['stay_in_session_message'] ?? null) {
                    $botReplyType = __tr('Stay in Session');
                }
                return $botReplyType;
            },
        ];
        // prepare data for the DataTables
        return $this->dataTableResponse($botReplyCollection, $requireColumns);
    }


    /**
      * BotReply delete process
      *
      * @param  mix $botReplyIdOrUid
      *
      * @return  EngineResponse
      *---------------------------------------------------------------- */

    public function processBotReplyDelete($botReplyIdOrUid, $botFlowId = null)
      {
          // fetch the record by UID and flow instead of vendor
          $conditions = ['_uid' => $botReplyIdOrUid];
          if ($botFlowId) {
              $conditions['bot_flows__id'] = $botFlowId;
          }
      
          $botReply = $this->botReplyRepository->fetchIt($conditions);
      
          if (__isEmpty($botReply)) {
              return $this->engineResponse(18, [
                  'botReplyUid' => $botReplyIdOrUid
              ], __tr('Bot Reply not found in this flow'));
          }
      
          if (isDemo() and in_array($botReply->_id, explode(',', config('__tech.demo_protected_bots')))) {
              return $this->engineResponse(2, null, __tr('You are not allowed to delete this bot in DEMO.'));
          }
      
          if ($this->botReplyRepository->deleteIt($botReply)) {
              $this->botReplyRepository->updateItAll(
                  ['bot_replies__id' => $botReply->_id],
                  ['reply_trigger' => null]
              );
      
              return $this->engineResponse(1, [
                  'botReplyUid' => $botReply->_uid
              ], __tr('Bot Reply deleted successfully from this flow'));
          }
      
          return $this->engineResponse(2, [
              'botReplyUid' => $botReplyIdOrUid
          ], __tr('Failed to delete Bot Reply'));
      }
     
    /**
      * BotReply duplicate process
      *
      * @param  mix $botReplyIdOrUid
      *
      * @return  EngineResponse
      *---------------------------------------------------------------- */
 public function processBotReplyDuplicate($botReplyIdOrUid, $contextFlowUid = null)
      {
          $vendorId = getVendorId();

          // fetch the record only by UID (no vendor restriction)
          $botReply = $this->botReplyRepository->fetchIt([
              '_uid' => $botReplyIdOrUid,
          ]);

          if (__isEmpty($botReply)) {
              return $this->engineResponse(18, [
                  'botReplyUid' => $botReplyIdOrUid
              ], __tr('Bot Reply not found'));
          }

          // Determine the target flow for duplication
          $currentBotFlowId = null;

          // First, try to use context flow if provided (from flow builder)
          if ($contextFlowUid) {
              $contextFlow = $this->botFlowRepository->fetchIt([
                  '_uid' => $contextFlowUid,
                  'vendors__id' => $vendorId,
              ]);

              if (!__isEmpty($contextFlow)) {
                  $currentBotFlowId = $contextFlow->_id;
              }
          }

          // If no context flow or context flow not found, try original flow
          if (!$currentBotFlowId && $botReply->bot_flows__id) {
              $originalFlow = $this->botFlowRepository->fetchIt([
                  '_id' => $botReply->bot_flows__id,
                  'vendors__id' => $vendorId,
              ]);

              if (!__isEmpty($originalFlow)) {
                  $currentBotFlowId = $originalFlow->_id;
              }
          }
      
          // if reply is outside any flow, check vendor plan limits
          if (!$currentBotFlowId) {
              $vendorPlanDetails = vendorPlanDetails(
                  'bot_replies',
                  $this->botReplyRepository->countIt([
                      'vendors__id' => $vendorId,
                      'bot_flows__id' => null,
                  ]),
                  $vendorId
              );
      
              if (!$vendorPlanDetails['is_limit_available']) {
                  return $this->engineResponse(22, null, $vendorPlanDetails['message']);
              }
          }
      
          // duplicate the record
          $newBotUid = Str::uuid();
          $newBotReply = $botReply->replicate();
      
          // enforce vendor + flow ownership
          $newBotReply->_uid = $newBotUid;
          $newBotReply->vendors__id = $vendorId;            // always current vendor
          $newBotReply->bot_flows__id = $currentBotFlowId;  // flow under current vendor
          $newBotReply->setAttribute('vendors__id', $vendorId);
          $newBotReply->setAttribute('bot_flows__id', $currentBotFlowId);
      
          // give it a new name
          $newBotReply->name = $botReply->name . '-' . uniqid();
      
          // clean up bot data
          $botData = $botReply->__data;
          if (is_array($botData)) {
              unset($botData['vendor_id'], $botData['original_vendor_id'], $botData['original_flow_id']);
      
              // clear buttons
              if (isset($botData['interaction_message']['buttons'])) {
                  $botData['interaction_message']['buttons'] = [];
              }
      
              // clear list data
              if (isset($botData['interaction_message']['list_data'])) {
                  $botData['interaction_message']['list_data'] = [
                      'button_text' => $botData['interaction_message']['list_data']['button_text'] ?? '',
                  ];
              }
          }
      
          if ($currentBotFlowId) {
              $newBotReply->reply_trigger = null;
              $newBotReply->status = 2; // inactive in flows
              $newBotReply->__data = $botData;
          }
      
          // save and return response
          if ($newBotReply->save()) {
              if ($currentBotFlowId) {
                  return $this->engineResponse(21, [
                      'reloadPage' => true,
                      'messageType' => 'success',
                  ], __tr('Bot Reply duplicated in this flow'));
              }
      
              return $this->engineResponse(1, [
                  'botReplyUid' => $newBotUid
              ], __tr('Bot Reply duplicated'));
          }
      
          return $this->engineResponse(2, [
              'botReplyUid' => $botReplyIdOrUid
          ], __tr('Failed to duplicate Bot Reply'));
      }
      
      

    /**
      * BotReply create
      *
      * @param  array $inputData
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotReplyCreate($request)
    {
        $inputData = $request->all();
        $vendorId = getVendorId();
        $inputData['status'] = ($inputData['status'] ?? null) ? 1 : 2;
        // if bot flow
        if(isset($inputData['bot_flow_uid']) and $inputData['bot_flow_uid']) {
            $botFlow = $this->botFlowRepository->fetchIt($inputData['bot_flow_uid']);
            if (__isEmpty($botFlow)) {
                return $this->engineResponse(2, null, __tr('Invalid bot flow'));
            }
            $inputData['bot_flows__id'] = $botFlow->_id;

            $request->validate([
                'name' => [
                    "required",
                    "max:200",
                    Rule::unique('bot_replies')->where(fn (Builder $query) => $query->where([
                        'vendors__id' => $vendorId,
                        'bot_flows__id' => $botFlow->_id,
                    ]))
                ]
            ]);
            // bot flow bot always in inactive state, its state will be depend on flow status
            $inputData['status'] = 2;
        }
        $messageType = $inputData['message_type'] ?? 'simple';
        if (!empty($inputData['whatsapp_flow_id'])) {
            $messageType = 'flow';
        }
        // do not apply plan restriction if bot is getting added for bot flow
        // as there no limit for flows
        if(!isset($inputData['bot_flow_uid']) or !$inputData['bot_flow_uid']) {
            // check the feature limit
            $vendorPlanDetails = vendorPlanDetails('bot_replies', $this->botReplyRepository->countIt([
                'vendors__id' => $vendorId,
                'bot_flows__id' => null,
            ]), $vendorId);
            if (!$vendorPlanDetails['is_limit_available']) {
                return $this->engineResponse(22, null, $vendorPlanDetails['message']);
            }
        }
        $inputData['vendors__id'] = $vendorId;
        if($messageType == 'interactive') {
            $interactiveType = $inputData['interactive_type'] ?? 'button';
            $mediaLink = '';
            if($inputData['header_type'] and ($inputData['header_type'] != 'text')) {
                $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $inputData['uploaded_media_file_name']], 'whatsapp_' . $inputData['header_type']);
                if ($isProcessed->failed()) {
                    return $isProcessed;
                }
                $mediaLink = $isProcessed->data('path');
            }
            $ctaUrlButton = null;
            $listData = null;
            if($interactiveType == 'cta_url') {
                $ctaUrlButton = [
                    'display_text' => $inputData['button_display_text'],
                    'url' => $inputData['button_url'],
                ];
            }
            if($interactiveType == 'list') {
                $listData = [
                    'button_text' => $inputData['list_button_text'],
                    'sections' => array_filter($inputData['sections'] ?? []),
                ];
            }
            $inputData['__data'] = [
                'interaction_message' => [
                    'interactive_type' => $interactiveType,
                    'media_link' => $mediaLink,
                    'header_type' => $inputData['header_type'], // "text", "image", or "video"
                    'header_text' => $inputData['header_text'] ?? '',
                    'body_text' => $inputData['reply_text'],
                    'footer_text' => $inputData['footer_text'] ?? '',
                    'buttons' => array_filter($inputData['buttons'] ?? []),
                    'cta_url' => $ctaUrlButton,
                    'list_data' => $listData,
                ]
            ];
        } elseif($messageType == 'media') {
            $inputData['header_type'] = $inputData['media_header_type'];
            $mediaLink = '';
            $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $inputData['uploaded_media_file_name']], 'whatsapp_' . $inputData['header_type']);
            if ($isProcessed->failed()) {
                return $isProcessed;
            }
            $mediaLink = $isProcessed->data('path');
            $inputData['reply_text'] = '';
            $inputData['__data'] = [
                'media_message' => [
                    'media_link' => $mediaLink,
                    'header_type' => $inputData['header_type'], // "text", "image", "audio or "video"
                    'caption' => $inputData['caption'] ?? '',
                    'file_name' => $isProcessed->data('fileName'),
                ]
            ];
        } elseif($messageType == 'flow') {
            $request->validate([
                'whatsapp_flow_id' => 'required|string',
            ]);

            $flowId = $inputData['whatsapp_flow_id'];
            $flowService = app(WhatsAppFlowService::class);
            $flowDetails = $flowService->getFlowDetails($flowId, $vendorId) ?? [];

            $flowName = $inputData['flow_name'] ?? ($flowDetails['name'] ?? '');
            $flowStatus = $inputData['flow_status'] ?? ($flowDetails['status'] ?? 'UNKNOWN');
            $flowCategories = $flowDetails['categories'] ?? ($inputData['flow_categories'] ?? []);

            $inputData['reply_text'] = $inputData['flow_body_text'] ?? '';
            $inputData['__data'] = [
                'flow_message' => [
                    'whatsapp_flow_id' => $flowId,
                    'flow_name' => $flowName,
                    'flow_status' => $flowStatus,
                    'flow_categories' => $flowCategories,
                    'header_text' => $inputData['flow_header_text'] ?? '',
                    'body_text' => $inputData['flow_body_text'] ?? '',
                    'footer_text' => $inputData['flow_footer_text'] ?? '',
                ]
            ];

            unset(
                $inputData['whatsapp_flow_id'],
                $inputData['flow_header_text'],
                $inputData['flow_body_text'],
                $inputData['flow_footer_text'],
                $inputData['flow_name'],
                $inputData['flow_status'],
                $inputData['flow_categories']
            );
        } elseif($messageType == 'goto') {
            // Validate that target node exists
            if (empty($inputData['goto_target_node'])) {
                return $this->engineResponse(2, null, __tr('Target node is required for goto nodes'));
            }

            // Verify target node exists in the same bot flow
            $targetNode = $this->botReplyRepository->fetchIt([
                '_uid' => $inputData['goto_target_node'],
                'bot_flows__id' => $inputData['bot_flows__id'] ?? null,
                'vendors__id' => $vendorId,
            ]);

            if (__isEmpty($targetNode)) {
                return $this->engineResponse(2, null, __tr('Selected target node does not exist'));
            }

            $inputData['reply_text'] = '';
            $inputData['__data'] = [
                'goto_message' => [
                    'redirect_to_node' => $inputData['goto_target_node'],
                    'target_node_name' => $targetNode->name,
                ]
            ];
        } elseif($messageType == 'question') {
            $conditionalFlows = [];
            if (!empty($inputData['conditional_flows'])) {
                foreach ($inputData['conditional_flows'] as $flow) {
                    if (!empty($flow['condition_value']) && !empty($flow['target_node'])) {
                        $conditionalFlows[] = [
                            'label' => $flow['label'] ?? '',
                            'condition_type' => $flow['condition_type'] ?? 'equals',
                            'condition_value' => $flow['condition_value'],
                            'target_node' => $flow['target_node']
                        ];
                    }
                }
            }

            $inputData['__data'] = [
                'question_message' => [
                    'variable_name' => $inputData['question_variable_name'] ?? '',
                    'input_type' => $inputData['question_input_type'] ?? 'text',
                    'validation_rules' => [
                        'min_length' => (int)($inputData['question_min_length'] ?? 1),
                        'max_length' => (int)($inputData['question_max_length'] ?? 500),
                    ],
                    'placeholder_text' => $inputData['question_placeholder'] ?? '',
                    'error_message' => $inputData['question_error_message'] ?? '',
                    'success_message' => $inputData['question_success_message'] ?? '',
                    'store_in_field' => $inputData['question_store_field'] ?? '',
                    'is_required' => (bool)($inputData['question_is_required'] ?? true),
                    'conditional_flows' => $conditionalFlows,
                    'default_next_node' => $inputData['question_default_next_node'] ?? null,
                ]
            ];
        } elseif($messageType == 'wait') {
            // For wait nodes, use the wait_message field or fallback to default
            $waitMessage = $inputData['wait_message'] ?? 'Please wait...';
            $inputData['reply_text'] = $waitMessage; // Set the main reply_text field
            
            $inputData['__data'] = [
                'wait_message' => [
                    'wait_delay_seconds' => (int)($inputData['wait_delay_seconds'] ?? 5),
                    'wait_message' => $waitMessage,
                ]
            ];
        } elseif($messageType == 'team_assignment') {
            // Validate that the assigned team member exists and has messaging permission
            if (empty($inputData['assigned_team_member'])) {
                return $this->engineResponse(2, null, __tr('Please select a team member to assign the conversation to'));
            }

            $assignedTeamMember = $this->userRepository->getVendorUserByUid(
                $inputData['assigned_team_member'],
                $vendorId
            );

            if (__isEmpty($assignedTeamMember)) {
                // Debug logging to help identify the issue
                Log::info('Team member not found during validation', [
                    'selected_team_member_uid' => $inputData['assigned_team_member'],
                    'vendor_id' => $vendorId,
                ]);

                return $this->engineResponse(2, null, __tr('Selected team member not found. Please ensure the team member exists and belongs to your organization.'));
            }

            // Verify the team member has messaging permission
            $vendorTeamMembers = $this->userRepository->getVendorMessagingUsers($vendorId);
            $hasMessagingPermission = $vendorTeamMembers->where('_uid', $inputData['assigned_team_member'])->count() > 0;

            if (!$hasMessagingPermission) {
                // Debug logging to help identify the issue
                Log::info('Team assignment validation failed', [
                    'selected_team_member_uid' => $inputData['assigned_team_member'],
                    'vendor_id' => $vendorId,
                    'available_team_members' => $vendorTeamMembers->pluck('_uid')->toArray(),
                    'team_member_found_in_users' => !__isEmpty($assignedTeamMember),
                ]);

                return $this->engineResponse(2, null, __tr('Selected team member does not have messaging permission. Please ensure the team member has messaging permission enabled in their user settings.'));
            }

            // For team assignment nodes, use the assignment_message or fallback to default
            $assignmentMessage = $inputData['assignment_message'] ?? '';
            $inputData['reply_text'] = $assignmentMessage ?: ''; // Set the main reply_text field or empty string
            
            $inputData['__data'] = [
                'team_assignment_message' => [
                    'assignment_message' => $assignmentMessage,
                    'assigned_team_member' => $inputData['assigned_team_member'],
                    'assigned_team_member_name' => $assignedTeamMember->full_name,
                    'assigned_team_member_email' => $assignedTeamMember->email,
                ]
            ];
        } elseif($messageType == 'webhook') {
            // Validate webhook URL
            if (empty($inputData['webhook_url'])) {
                return $this->engineResponse(2, null, __tr('Webhook URL is required'));
            }

            if (!filter_var($inputData['webhook_url'], FILTER_VALIDATE_URL)) {
                return $this->engineResponse(2, null, __tr('Invalid webhook URL format'));
            }

            // Process response mapping
            $responseMapping = [];
            if (!empty($inputData['response_mapping'])) {
                foreach ($inputData['response_mapping'] as $mapping) {
                    if (!empty($mapping['source_path']) && !empty($mapping['target_variable'])) {
                        $responseMapping[] = [
                            'source_path' => $mapping['source_path'],
                            'target_variable' => $mapping['target_variable']
                        ];
                    }
                }
            }

            // For webhook nodes, use the success_message or fallback to default
            $webhookMessage = $inputData['success_message'] ?? 'Processing webhook...';
            $inputData['reply_text'] = $webhookMessage; // Set the main reply_text field
            
            $inputData['__data'] = [
                'webhook_message' => [
                    'webhook_url' => $inputData['webhook_url'],
                    'http_method' => $inputData['http_method'] ?? 'POST',
                    'request_body' => $inputData['request_body'] ?? '{}',
                    'timeout' => (int)($inputData['timeout'] ?? 30),
                    'success_message' => $webhookMessage,
                    'error_message' => $inputData['error_message'] ?? 'Webhook execution failed',
                    'response_mapping' => $responseMapping,
                ]
            ];
        } elseif($messageType == 'custom_field') {
            // Validate custom field selection
            if (empty($inputData['custom_field_id'])) {
                return $this->engineResponse(2, null, __tr('Custom field selection is required'));
            }

            if (empty($inputData['question_text'])) {
                return $this->engineResponse(2, null, __tr('Question text is required'));
            }

            // Verify the custom field exists and belongs to the vendor
            $customField = $this->contactCustomFieldRepository->fetchIt([
                '_id' => $inputData['custom_field_id'],
                'vendors__id' => $vendorId
            ]);

            if (__isEmpty($customField)) {
                return $this->engineResponse(2, null, __tr('Selected custom field not found. Please ensure the custom field exists and belongs to your organization.'));
            }

            // For custom field nodes, use the question_text
            $questionText = $inputData['question_text'];
            $inputData['reply_text'] = $questionText; // Set the main reply_text field
            
            $inputData['__data'] = [
                'custom_field_message' => [
                    'custom_field_id' => $inputData['custom_field_id'],
                    'custom_field_name' => $inputData['custom_field_name'] ?? $customField->input_name,
                    'custom_field_type' => $customField->input_type,
                    'question_text' => $questionText,
                ]
            ];
        } elseif($messageType == 'whatsapp_template') {
            // Validate WhatsApp template selection
            if (empty($inputData['whatsapp_template_id'])) {
                return $this->engineResponse(2, null, __tr('WhatsApp template selection is required'));
            }

            // Verify the template exists and belongs to the vendor
            $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt([
                '_id' => $inputData['whatsapp_template_id'],
                'vendors__id' => $vendorId
            ]);

            if (__isEmpty($whatsAppTemplate)) {
                return $this->engineResponse(2, null, __tr('Selected WhatsApp template not found. Please ensure the template exists and belongs to your organization.'));
            }

            // For WhatsApp template nodes, set a default reply text
            $inputData['reply_text'] = __tr('Available WhatsApp Templates:');
            
            $inputData['__data'] = [
                'whatsapp_template_message' => [
                    'whatsapp_template_id' => $inputData['whatsapp_template_id'],
                    'template_name' => $whatsAppTemplate->template_name,
                    'template_language' => $whatsAppTemplate->language,
                ]
            ];
        } elseif($messageType == 'stay_in_session') {
            // For stay in session nodes, use the session_message field or fallback to empty
            $sessionMessage = $inputData['session_message'] ?? '';
            $inputData['reply_text'] = ''; // Stay in session nodes don't need reply_text

            $inputData['__data'] = [
                'stay_in_session_message' => [
                    'session_message' => $sessionMessage,
                ]
            ];
        }
        // ask to add record
        $engineResponse = $this->botReplyRepository->processTransaction(function () use (&$inputData, &$vendorId) {
            if ($botReply = $this->botReplyRepository->storeBotReply($inputData)) {
                // if needs to validate message using by sending test message
                if($inputData['validate_bot_reply'] ?? null) {
                    $validateTestBotReply = $this->whatsAppServiceEngine->validateTestBotReply($botReply->_id);
                    if($validateTestBotReply->success()) {
                        return $this->botReplyRepository->transactionResponse(1, [], __tr('Bot Reply Created'));
                    }
                    // if got any errors etc
                    return $this->botReplyRepository->transactionResponse($validateTestBotReply->reaction(), [], $validateTestBotReply->message());
                }
                // success
                return $this->botReplyRepository->transactionResponse(1, [], __tr('Bot Reply Created'));
            }
            // failed for any other reason
            return $this->botReplyRepository->transactionResponse(2, [], __tr('Failed to create Bot Reply'));
        });
        // if bot flow
        if(isset($inputData['bot_flow_uid']) and $inputData['bot_flow_uid'] and ($engineResponse[0] == 1)) {
            $flowBots = $this->botReplyRepository->fetchItAll([
                'bot_flows__id' => $inputData['bot_flows__id'],
                'vendors__id' => $vendorId,
            ]);

            $responseData = [
                'flowBots' => $flowBots,
            ];

            // If it's a goto node, add auto-connection data
            if ($messageType === 'goto' && !empty($inputData['goto_target_node'])) {
                $responseData['autoConnectGoto'] = [
                    'gotoNodeId' => $engineResponse[1]['_uid'] ?? null,
                    'targetNodeId' => $inputData['goto_target_node']
                ];
            }

            updateClientModels($responseData);
        }
        return $this->engineResponse($engineResponse);

    }

    /**
      * BotReply prepare update data
      *
      * @param  mix $botReplyIdOrUid
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function prepareBotReplyUpdateData($botReplyIdOrUid)
    {
        $vendorId = getVendorId();
        // fetch the record
        $botReply = $this->botReplyRepository->fetchIt([
            '_uid' => $botReplyIdOrUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $botReply not exist then throw not found
        // exception
        if (__isEmpty($botReply)) {
            return $this->engineResponse(18, null, __tr('Bot Reply not found.'));
        }

        return $this->engineResponse(1, $botReply->toArray());
    }

    /**
      * BotReply process update
      *
      * @param  mixed $botReplyIdOrUid
      * @param  object $request
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotReplyUpdate($botReplyIdOrUid, $request)
    {
        $vendorId = getVendorId();
        // fetch the record
        $botReply = $this->botReplyRepository->fetchIt([
            '_uid' => $botReplyIdOrUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $botReply not exist then throw not found
        // exception
        if (__isEmpty($botReply)) {
            return $this->engineResponse(18, null, __tr('Bot Reply not found.'));
        }
        // demo bot edit protection
        if(isDemo() and in_array($botReply->_id, explode(',', config('__tech.demo_protected_bots')))) {
            return $this->engineResponse(2, null, __tr('Your are not allowed to edit this bot in DEMO.'));
        }
        $botFlowId = null;
        $inputData = $request->all();
        // if bot flow
        if(isset($inputData['bot_flow_uid']) and $inputData['bot_flow_uid']) {
            $botFlow = $this->botFlowRepository->fetchIt($inputData['bot_flow_uid']);
            if (__isEmpty($botFlow)) {
                return $this->engineResponse(2, null, __tr('Invalid bot flow'));
            }
            $botFlowId = $botFlow->_id;
        }
        $currentInputSectionsData = array_filter($inputData['sections'] ?? []);
        $currentInputButtonsData = array_filter($inputData['buttons'] ?? []);
        if($botFlowId) {
            // validate for uniqueness
            $request->validate([
                "name" => [
                    "required",
                    "max:200",
                    Rule::unique('bot_replies')->where(fn (Builder $query) => $query->where([
                        'vendors__id' => $vendorId,
                        'bot_flows__id'  => $botFlowId,
                    ]))->ignore($botReply->_id, '_id')
                ],
            ]);
        } else {
            $request->validate([
                "name" => [
                    "required",
                    "max:200",
                    Rule::unique('bot_replies')->where(fn (Builder $query) => $query->where([
                        'vendors__id' => $vendorId,
                        'bot_flows__id'  => null,
                    ]))->ignore($botReply->_id, '_id')
                ],
            ]);
        }

        $messageType = $inputData['message_type'] ?? 'simple';
        if (!empty($inputData['whatsapp_flow_id'])) {
            $messageType = 'flow';
        }
        $updateData = [
            'name' => $inputData['name'],
            'reply_text' => $inputData['reply_text'] ?? '',
        ];
        if(!$botFlowId) {
            $updateData['trigger_type'] = $request->trigger_type;
            $updateData['reply_trigger'] = $request->reply_trigger;
            $updateData['status'] = $request->status ? 1 : 2;
        }
        // media message
        if($messageType == 'media') {
            $updateData['__data'] = [
                'media_message' => [
                    'caption' => $inputData['caption'] ?? '',
                ]
            ];
        } elseif($messageType == 'flow') {
            $request->validate([
                'whatsapp_flow_id' => 'required|string',
            ]);

            $flowId = $inputData['whatsapp_flow_id'];
            $flowService = app(WhatsAppFlowService::class);
            $flowDetails = $flowService->getFlowDetails($flowId, $vendorId) ?? [];

            $flowName = $inputData['flow_name'] ?? ($flowDetails['name'] ?? '');
            $flowStatus = $inputData['flow_status'] ?? ($flowDetails['status'] ?? 'UNKNOWN');
            $flowCategories = $flowDetails['categories'] ?? ($inputData['flow_categories'] ?? []);

            $updateData['reply_text'] = $inputData['flow_body_text'] ?? '';
            $updateData['__data'] = [
                'flow_message' => [
                    'whatsapp_flow_id' => $flowId,
                    'flow_name' => $flowName,
                    'flow_status' => $flowStatus,
                    'flow_categories' => $flowCategories,
                    'header_text' => $inputData['flow_header_text'] ?? '',
                    'body_text' => $inputData['flow_body_text'] ?? '',
                    'footer_text' => $inputData['flow_footer_text'] ?? '',
                ]
            ];

            unset(
                $inputData['whatsapp_flow_id'],
                $inputData['flow_header_text'],
                $inputData['flow_body_text'],
                $inputData['flow_footer_text'],
                $inputData['flow_name'],
                $inputData['flow_status'],
                $inputData['flow_categories']
            );
        } elseif($messageType == 'goto') {
            // Validate that target node exists
            if (empty($inputData['goto_target_node'])) {
                return $this->engineResponse(2, null, __tr('Target node is required for goto nodes'));
            }

            // Verify target node exists in the same bot flow
            $targetNode = $this->botReplyRepository->fetchIt([
                '_uid' => $inputData['goto_target_node'],
                'bot_flows__id' => $botFlowId,
                'vendors__id' => $vendorId,
            ]);

            if (__isEmpty($targetNode)) {
                return $this->engineResponse(2, null, __tr('Selected target node does not exist'));
            }

            $updateData['reply_text'] = '';
            $updateData['__data'] = [
                'goto_message' => [
                    'redirect_to_node' => $inputData['goto_target_node'],
                    'target_node_name' => $targetNode->name,
                ]
            ];
        } elseif($messageType == 'question') {
            $conditionalFlows = [];
            if (!empty($inputData['conditional_flows'])) {
                foreach ($inputData['conditional_flows'] as $flow) {
                    if (!empty($flow['condition_value']) && !empty($flow['target_node'])) {
                        $conditionalFlows[] = [
                            'label' => $flow['label'] ?? '',
                            'condition_type' => $flow['condition_type'] ?? 'equals',
                            'condition_value' => $flow['condition_value'],
                            'target_node' => $flow['target_node']
                        ];
                    }
                }
            }

            $updateData['__data'] = [
                'question_message' => [
                    'variable_name' => $inputData['question_variable_name'] ?? '',
                    'input_type' => $inputData['question_input_type'] ?? 'text',
                    'validation_rules' => [
                        'min_length' => (int)($inputData['question_min_length'] ?? 1),
                        'max_length' => (int)($inputData['question_max_length'] ?? 500),
                    ],
                    'placeholder_text' => $inputData['question_placeholder'] ?? '',
                    'error_message' => $inputData['question_error_message'] ?? '',
                    'success_message' => $inputData['question_success_message'] ?? '',
                    'store_in_field' => $inputData['question_store_field'] ?? '',
                    'is_required' => (bool)($inputData['question_is_required'] ?? true),
                    'conditional_flows' => $conditionalFlows,
                    'default_next_node' => $inputData['question_default_next_node'] ?? null,
                ]
            ];
        } elseif($messageType == 'wait') {
            $updateData['__data'] = [
                'wait_message' => [
                    'wait_delay_seconds' => (int)($inputData['wait_delay_seconds'] ?? 5),
                    'wait_message' => $inputData['wait_message'] ?? $inputData['reply_text'] ?? 'Please wait...',
                ]
            ];
        } elseif($messageType == 'team_assignment') {
            // Validate that the assigned team member exists and has messaging permission
            if (empty($inputData['assigned_team_member'])) {
                return $this->engineResponse(2, null, __tr('Please select a team member to assign the conversation to'));
            }

            $assignedTeamMember = $this->userRepository->getVendorUserByUid(
                $inputData['assigned_team_member'],
                $vendorId
            );

            if (__isEmpty($assignedTeamMember)) {
                // Debug logging to help identify the issue
                Log::info('Team member not found during update validation', [
                    'selected_team_member_uid' => $inputData['assigned_team_member'],
                    'vendor_id' => $vendorId,
                ]);

                return $this->engineResponse(2, null, __tr('Selected team member not found. Please ensure the team member exists and belongs to your organization.'));
            }

            // Verify the team member has messaging permission
            $vendorTeamMembers = $this->userRepository->getVendorMessagingUsers($vendorId);
            $hasMessagingPermission = $vendorTeamMembers->where('_uid', $inputData['assigned_team_member'])->count() > 0;

            if (!$hasMessagingPermission) {
                // Debug logging to help identify the issue
                Log::info('Team assignment update validation failed', [
                    'selected_team_member_uid' => $inputData['assigned_team_member'],
                    'vendor_id' => $vendorId,
                    'available_team_members' => $vendorTeamMembers->pluck('_uid')->toArray(),
                    'team_member_found_in_users' => !__isEmpty($assignedTeamMember),
                ]);

                return $this->engineResponse(2, null, __tr('Selected team member does not have messaging permission. Please ensure the team member has messaging permission enabled in their user settings.'));
            }

            $updateData['reply_text'] = $inputData['assignment_message'] ?? '';
            $updateData['__data'] = [
                'team_assignment_message' => [
                    'assignment_message' => $inputData['assignment_message'] ?? '',
                    'assigned_team_member' => $inputData['assigned_team_member'],
                    'assigned_team_member_name' => $assignedTeamMember->full_name,
                    'assigned_team_member_email' => $assignedTeamMember->email,
                ]
            ];
        } elseif($messageType == 'webhook') {
            // Validate webhook URL
            if (empty($inputData['webhook_url'])) {
                return $this->engineResponse(2, null, __tr('Webhook URL is required'));
            }

            if (!filter_var($inputData['webhook_url'], FILTER_VALIDATE_URL)) {
                return $this->engineResponse(2, null, __tr('Invalid webhook URL format'));
            }

            // Process response mapping
            $responseMapping = [];
            if (!empty($inputData['response_mapping'])) {
                foreach ($inputData['response_mapping'] as $mapping) {
                    if (!empty($mapping['source_path']) && !empty($mapping['target_variable'])) {
                        $responseMapping[] = [
                            'source_path' => $mapping['source_path'],
                            'target_variable' => $mapping['target_variable']
                        ];
                    }
                }
            }

            $updateData['__data'] = [
                'webhook_message' => [
                    'webhook_url' => $inputData['webhook_url'],
                    'http_method' => $inputData['http_method'] ?? 'POST',
                    'request_body' => $inputData['request_body'] ?? '{}',
                    'timeout' => (int)($inputData['timeout'] ?? 30),
                    'success_message' => $inputData['success_message'] ?? 'Webhook executed successfully',
                    'error_message' => $inputData['error_message'] ?? 'Webhook execution failed',
                    'response_mapping' => $responseMapping,
                ]
            ];
        } elseif($messageType == 'custom_field') {
            // Validate custom field selection
            if (empty($inputData['custom_field_id'])) {
                return $this->engineResponse(2, null, __tr('Custom field selection is required'));
            }

            if (empty($inputData['question_text'])) {
                return $this->engineResponse(2, null, __tr('Question text is required'));
            }

            // Verify the custom field exists and belongs to the vendor
            $customField = $this->contactCustomFieldRepository->fetchIt([
                '_id' => $inputData['custom_field_id'],
                'vendors__id' => $vendorId
            ]);

            if (__isEmpty($customField)) {
                return $this->engineResponse(2, null, __tr('Selected custom field not found. Please ensure the custom field exists and belongs to your organization.'));
            }

            $updateData['__data'] = [
                'custom_field_message' => [
                    'custom_field_id' => $inputData['custom_field_id'],
                    'custom_field_name' => $inputData['custom_field_name'] ?? $customField->input_name,
                    'custom_field_type' => $customField->input_type,
                    'question_text' => $inputData['question_text'],
                ]
            ];
        } elseif($messageType == 'whatsapp_template') {
            // Validate WhatsApp template selection
            if (empty($inputData['whatsapp_template_id'])) {
                return $this->engineResponse(2, null, __tr('WhatsApp template selection is required'));
            }

            // Verify the template exists and belongs to the vendor
            $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt([
                '_id' => $inputData['whatsapp_template_id'],
                'vendors__id' => $vendorId
            ]);

            if (__isEmpty($whatsAppTemplate)) {
                return $this->engineResponse(2, null, __tr('Selected WhatsApp template not found. Please ensure the template exists and belongs to your organization.'));
            }

            // For WhatsApp template nodes, set a default reply text
            $updateData['reply_text'] = __tr('Available WhatsApp Templates:');
            
            $updateData['__data'] = [
                'whatsapp_template_message' => [
                    'whatsapp_template_id' => $inputData['whatsapp_template_id'],
                    'template_name' => $whatsAppTemplate->template_name,
                    'template_language' => $whatsAppTemplate->language,
                ]
            ];
        } elseif($messageType == 'interactive') {
            $interactiveType = $inputData['interactive_type'] ?? 'button';
            $ctaUrlButton = null;
            if($interactiveType == 'cta_url') {
                $ctaUrlButton = [
                    'display_text' => $inputData['button_display_text'],
                    'url' => $inputData['button_url'],
                ];
            }
            $listData = null;
            if($interactiveType == 'list') {
                $listData = [
                    'button_text' => $inputData['list_button_text'],
                    'sections' => $currentInputSectionsData,
                ];
            }
            $updateData['__data'] = [
                'interaction_message' => [
                    'interactive_type' => $interactiveType,
                    'header_text' => $inputData['header_text'] ?? '',
                    'body_text' => $inputData['reply_text'],
                    'footer_text' => $inputData['footer_text'] ?? '',
                    'buttons' => $currentInputButtonsData,
                    'cta_url' => $ctaUrlButton,
                    'list_data' => $listData,
                ]
            ];
        } elseif($messageType == 'stay_in_session') {
            // For stay in session nodes, update the session message
            $sessionMessage = $inputData['session_message'] ?? '';
            $updateData['reply_text'] = ''; // Stay in session nodes don't need reply_text

            $updateData['__data'] = [
                'stay_in_session_message' => [
                    'session_message' => $sessionMessage,
                ]
            ];
        }
        // update process
        $engineResponse = $this->botReplyRepository->processTransaction(function () use (&$botReply, &$updateData, &$request, &$interactiveType, &$currentInputButtonsData, &$botFlowId, &$botFlow, $currentInputSectionsData, &$inputData) {
            $isUpdated = false;
            $botFlowData = $botFlow->__data ?? [];
            $botData = $botReply->__data;
            $existingLinks = $botFlowData['flow_builder_data']['links'] ?? [];
            if($interactiveType == 'button') {
                // update links etc for bot flow
                if($botFlowId) {
                    $existingButtons = $botReply->__data['interaction_message']['buttons'] ?? [];
                    $buttonsRemoved = array_diff($existingButtons, $currentInputButtonsData);
                    $buttonsAdded = array_diff($currentInputButtonsData, $existingButtons);
                    $existingBotsForTrigger = $this->botReplyRepository->fetchItAll($existingButtons, [], 'reply_trigger', [
                        'where' => [
                            'bot_flows__id' => $botFlowId,
                            'bot_replies__id' => $botReply->_id,
                        ]
                    ]);
                    foreach ($existingBotsForTrigger as $existingBotForTrigger) {
                        $itemIndexInRemoved = array_search($existingBotForTrigger->reply_trigger, $buttonsRemoved);
                        if($itemIndexInRemoved !== false) {
                            $newReplyTrigger = $buttonsAdded[$itemIndexInRemoved] ?? null;
                            $isUpdated = $this->botReplyRepository->updateIt($existingBotForTrigger, [
                                'reply_trigger' => $newReplyTrigger
                            ]);
                            if(!$newReplyTrigger) {
                                if(!empty($existingLinks)) {
                                    $existingLinks = Arr::where($existingLinks, function ($value, $key) use (&$existingBotForTrigger) {
                                        return $value['toOperator'] != $existingBotForTrigger['_uid'];
                                    });
                                }
                            }
                        }
                    }
                    // update flow links
                    // following type of method to update JSON creates problem for mariadb 
                    //  '__data->flow_builder_data->links' => $existingLinks
                    // instead we have used it
                    Arr::set($botFlowData, 'flow_builder_data.links', $existingLinks);
                    $isUpdated = $this->botFlowRepository->updateBotFlowData($botFlowId, [
                        '__data' => json_encode($botFlowData)
                    ]);
                }
                // update buttons
                // following type of method to update JSON creates problem for mariadb 
                //  '__data->interaction_message->buttons' => $updateData['__data']['interaction_message']['buttons']
                // instead we have used it
                Arr::set($botData, 'interaction_message.buttons', $updateData['__data']['interaction_message']['buttons']);
                unset($updateData['__data']);
                if($request->has('footer_text') and !$request->footer_text) {
                    Arr::set($botData, 'interaction_message.footer_text', '');
                }
                $updateData['__data'] = json_encode($botData);
                $isUpdated = $this->botReplyRepository->updateForListAndButtonMessage($botReply->_id, $updateData);
            } elseif($interactiveType == 'list') {
                $listDataSections =  $botReply->__data['interaction_message']['list_data']['sections'] ?? [];
                $existingRowSubjects = [];
                foreach ($listDataSections as $listDataSection) {
                    foreach ($listDataSection['rows'] as $listDataSectionRow) {
                        $existingRowSubjects[] = $listDataSectionRow['title'];
                    }
                }
                $currentRowSubjects = [];
                foreach ($currentInputSectionsData as $currentInputSection) {
                    foreach ($currentInputSection['rows'] as $currentInputSectionRow) {
                        $currentRowSubjects[] = $currentInputSectionRow['title'];
                    }
                }

                $rowsRemoved = array_diff($existingRowSubjects, $currentRowSubjects);
                $rowsAdded = array_diff($currentRowSubjects, $existingRowSubjects);
                $existingBotsForTrigger = $this->botReplyRepository->fetchItAll($existingRowSubjects, [], 'reply_trigger', [
                    'where' => [
                        'bot_flows__id' => $botFlowId,
                        'bot_replies__id' => $botReply->_id,
                    ]
                ]);
                foreach ($existingBotsForTrigger as $existingBotForTrigger) {
                    $itemIndexInRemoved = array_search($existingBotForTrigger->reply_trigger, $rowsRemoved);
                    if($itemIndexInRemoved !== false) {
                        $newReplyTrigger = $rowsAdded[$itemIndexInRemoved] ?? null;
                        $isUpdated = $this->botReplyRepository->updateIt($existingBotForTrigger, [
                            'reply_trigger' => $newReplyTrigger
                        ]);
                        if(!$newReplyTrigger) {
                            if(!empty($existingLinks)) {
                                $existingLinks = Arr::where($existingLinks, function ($value, $key) use (&$existingBotForTrigger) {
                                    return $value['toOperator'] != $existingBotForTrigger['_uid'];
                                });
                            }
                        }
                    }
                }
                // update flow links
                // following type of method to update JSON creates problem for mariadb 
                //  '__data->flow_builder_data->links' => $existingLinks
                // instead we have used it
                Arr::set($botFlowData, 'flow_builder_data.links', $existingLinks);
                $isUpdated = $this->botFlowRepository->updateBotFlowData($botFlowId, [
                    '__data' => json_encode($botFlowData)
                ]);
                // following type of method to update JSON creates problem for mariadb 
                //  '__data->interaction_message->buttons' => $updateData['__data']['interaction_message']['buttons']
                // instead we have used it
                Arr::set($botData, 'interaction_message', $updateData['__data']['interaction_message']);
                unset($updateData['__data']);
                if($request->has('footer_text') and !$request->footer_text) {
                    Arr::set($botData, 'interaction_message.footer_text', '');
                }
                $updateData['__data'] = json_encode($botData);
                $isUpdated = $this->botReplyRepository->updateForListAndButtonMessage($botReply->_id, $updateData);
            } else {
                // Handle goto and question nodes, and other simple updates
                $isUpdated = $this->botReplyRepository->updateIt($botReply, $updateData);
                if($request->has('footer_text') and !$request->footer_text) {
                    $isUpdated = $this->botReplyRepository->updateForListAndButtonMessage($botReply->_id, [
                        '__data->interaction_message->footer_text' => ''
                    ]);
                }
            }
            if ($isUpdated) {
                if($request->validate_bot_reply) {
                    $validateTestBotReply = $this->whatsAppServiceEngine->validateTestBotReply($botReply->_id);
                    if($validateTestBotReply->success()) {
                        return $this->botReplyRepository->transactionResponse(1, [], __tr('Bot Reply updated.'));
                    }
                    return $this->botReplyRepository->transactionResponse($validateTestBotReply->reaction(), [], $validateTestBotReply->message());
                }
                return $this->botReplyRepository->transactionResponse(1, [], __tr('Bot Reply updated.'));
            }
            return $this->botReplyRepository->transactionResponse(2, [], __tr('Bot Reply Not updated.'));
        });
        // if bot flow
        if($botFlowId and ($engineResponse[0] == 1)) {
            $responseData = [
                'reloadPage' => true
            ];

            // If it's a goto node, add auto-connection data
            if ($messageType === 'goto' && !empty($inputData['goto_target_node'])) {
                $responseData['autoConnectGoto'] = [
                    'gotoNodeId' => $botReply->_uid,
                    'targetNodeId' => $inputData['goto_target_node']
                ];
            }

            return $this->engineResponse(21, $responseData);
        }
        return $this->engineResponse($engineResponse);
    }
}