<?php
/**
* BotFlowController.php - Controller file
*
* This file is part of the BotReply component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\BotReply\Controllers;

use Illuminate\Validation\Rule;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequestTwo;
use Illuminate\Database\Query\Builder;
use App\Yantrana\Components\BotReply\BotFlowEngine;
use App\Yantrana\Components\BotReply\BotReplyEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Components\Flows\Services\WhatsAppFlowService;
use Illuminate\Http\Request;


class BotFlowController extends BaseController
{       /**
     * @var  BotFlowEngine $botFlowEngine - BotFlow Engine
     */
    protected $botFlowEngine;

    /**
     * @var  BotReplyEngine $botReplyEngine - BotReply Engine
     */
    protected $botReplyEngine;

    /**
     * @var  UserRepository $userRepository - User Repository
     */
    protected $userRepository;

    /**
     * @var WhatsAppFlowService
     */
    protected $whatsappFlowService;

    /**
      * Constructor
      *
      * @param  BotFlowEngine $botFlowEngine - BotFlow Engine
      * @param  BotReplyEngine $botReplyEngine - BotReply Engine
      * @param  UserRepository $userRepository - User Repository
      *
      * @return  void
      *-----------------------------------------------------------------------*/
    public function __construct(
        BotFlowEngine $botFlowEngine,
        BotReplyEngine $botReplyEngine,
        UserRepository $userRepository,
        WhatsAppFlowService $whatsappFlowService
    ) {
        $this->botFlowEngine = $botFlowEngine;
        $this->botReplyEngine = $botReplyEngine;
        $this->userRepository = $userRepository;
        $this->whatsappFlowService = $whatsappFlowService;
    }


    /**
      * list of BotFlow
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function showBotFlowView()
    {
        // load the view
        return $this->loadView('bot-reply.bot-flow.list');
    }
    /**
      * list of BotFlow
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function prepareBotFlowList()
    {
        validateVendorAccess('manage_bot_replies');
        // respond with dataTables preparations
        return $this->botFlowEngine->prepareBotFlowDataTableSource();
    }

    /**
        * BotFlow process delete
        *
        * @param  mix $botFlowIdOrUid
        *
        * @return  json object
        *---------------------------------------------------------------- */

    public function processBotFlowDelete($botFlowIdOrUid, BaseRequest $request)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        // ask engine to process the request
        $processReaction = $this->botFlowEngine->processBotFlowDelete($botFlowIdOrUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    

   

    public function processBotFlowClone(BaseRequest $request, $botFlowIdOrUid)
    {
    validateVendorAccess('manage_bot_flows');
    $vendorId = getVendorId();

    if (isDemo() && isDemoVendorAccount()) {
        return $this->processResponse(22, [
            22 => __tr('Functionality is disabled in this demo.')
        ], [], true);
    }

    $processReaction = $this->botFlowEngine->processBotFlowClone($botFlowIdOrUid);

    return $this->processResponse($processReaction, [], [], true);
    }
    /**
        * BotFlow export process
        *
        * @param  mix $botFlowIdOrUid
        *
        * @return  json object
        *---------------------------------------------------------------- */

    public function processBotFlowExport($botFlowIdOrUid, BaseRequest $request)
    {
        validateVendorAccess('manage_bot_replies');
        // ask engine to process the request
        $processReaction = $this->botFlowEngine->processBotFlowExport($botFlowIdOrUid);

        // Check if successful
        if ($processReaction['reaction_code'] === 1) {
            $exportData = $processReaction['data']['exportData'];
            $filename = $processReaction['data']['filename'];

            // Return JSON response for download
            return response()->json($exportData)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
        * BotFlow import process
        *
        * @param  object BaseRequest $request
        *
        * @return  json object
        *---------------------------------------------------------------- */

        public function processBotFlowImport(Request $request)
        {
            validateVendorAccess('manage_bot_replies');
        
            if(isDemo() and isDemoVendorAccount()) {
                return $this->processResponse(22, [
                    22 => __tr('Functionality is disabled in this demo.')
                ], [], true);
            }
        
            // Validate file upload
            $request->validate([
                'import_file' => 'required|file|max:2048', // 2MB max
            ]);
        
            // Additional validation for JSON file
            $uploadedFile = $request->file('import_file');
            if ($uploadedFile->getClientOriginalExtension() !== 'json') {
                return $this->processResponse(2, [], [
                    'import_file' => [__tr('The file must be a JSON file.')]
                ], true);
            }
        
            // ask engine to process the request
            $processReaction = $this->botFlowEngine->processBotFlowImport($uploadedFile);
        
                     return redirect()
    ->back()
    ->with('success', 'Bot Flow imported successfully');
        }

    /**
      * BotFlow create process
      *
      * @param  object BaseRequest $request
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function processBotFlowCreate(BaseRequest $request)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $vendorId = getVendorId();

        // Prepare validation rules
        $validationRules = [
            'title' => [
                "required",
                "max:150",
                Rule::unique('bot_flows')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId))
            ],
            'trigger_type' => [
                "required",
                "in:" . implode(',', array_keys(configItem('bot_reply_trigger_types')))
            ]
        ];

        // Add start_trigger validation only if trigger_type is not 'welcome' or 'new_message'
        if (!in_array($request->trigger_type, ['welcome', 'new_message'])) {
            $validationRules['start_trigger'] = [
                "required",
                "max:255",
                Rule::unique('bot_flows')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId))
            ];
        }

        // process the validation based on the provided rules
        $request->validate($validationRules);
        // ask engine to process the request
        $processReaction = $this->botFlowEngine->processBotFlowCreate($request->all());
        // get back with response
        return $this->processResponse($processReaction);
    }

    /**
      * BotFlow get update data
      *
      * @param  mix $botFlowIdOrUid
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function updateBotFlowData($botFlowIdOrUid)
    {
        validateVendorAccess('manage_bot_replies');
        // ask engine to process the request
        $processReaction = $this->botFlowEngine->prepareBotFlowUpdateData($botFlowIdOrUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
      * BotFlow process update
      *
      * @param  mix @param  mix $botFlowIdOrUid
      * @param  object BaseRequest $request
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function processBotFlowUpdate(BaseRequest $request)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        // Prepare validation rules
        $validationRules = [
            'botFlowIdOrUid' => 'required',
            "title" => "required|max:150",
            'trigger_type' => [
                "required",
                "in:" . implode(',', array_keys(configItem('bot_reply_trigger_types')))
            ]
        ];

        // Add start_trigger validation only if trigger_type is not 'welcome' or 'new_message'
        if (!in_array($request->trigger_type, ['welcome', 'new_message'])) {
            $validationRules['start_trigger'] = "required|max:255";
        }

        // process the validation based on the provided rules
        $request->validate($validationRules);
        // ask engine to process the request
        $processReaction = $this->botFlowEngine->processBotFlowUpdate($request->get('botFlowIdOrUid'), $request);
        // get back with response
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
     * Bot Flow Data update for builder
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function botFlowDataUpdate(BaseRequestTwo $request)
    {
        validateVendorAccess('manage_bot_replies');
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        // process the validation based on the provided rules
        $request->validate([
            'botFlowUid' => 'required',
            "flow_chart_data" => "nullable|array",
        ]);
        // ask engine to process the request
        $processReaction = $this->botFlowEngine->processBotFlowDataUpdate($request);
        // get back with response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Flow Builder view
     *
     * @param BaseRequest $request
     * @param string $botFlowIdOrUid
     * @return view
     */
    public function flowBuilderView(BaseRequest $request, $botFlowIdOrUid)
    {
        validateVendorAccess('manage_bot_replies');
        $processReaction = $this->botFlowEngine->prepareBotFlowBuilderData($botFlowIdOrUid);
        abortIf($processReaction->failed());

        // Get vendor team members for team assignment nodes
        $vendorId = getVendorId();
        $vendorTeamMembers = $this->userRepository->getVendorMessagingUsers($vendorId);

        // load the view
        $preData = $this->botReplyEngine->preDataForBots();
        $flowResponse = $this->whatsappFlowService->listFlows();

        return $this->loadView('bot-reply.bot-flow.builder', array_merge([
            'dynamicFields' => $preData->data('dynamicFields'),
            'contactCustomFields' => $preData->data('contactCustomFields'),
            'whatsAppTemplates' => $preData->data('whatsAppTemplates'),
            'vendorTeamMembers' => $vendorTeamMembers,
            'botFlowUid' => $botFlowIdOrUid,
            'whatsAppFlows' => $flowResponse['flows'] ?? [],
            'whatsAppFlowsError' => $flowResponse['error'] ?? null,
        ], $processReaction->data()));
    }
}