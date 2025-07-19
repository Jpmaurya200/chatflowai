<?php
/**
* WhatsAppServiceController.php - Controller file
*
* This file is part of the WhatsAppService component.
*-----------------------------------------------------------------------------*/


namespace App\Yantrana\Components\WhatsAppService\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequestTwo;
use App\Yantrana\Components\Vendor\VendorSettingsEngine;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;
use App\Yantrana\Components\WhatsAppService\WhatsAppTemplateEngine;
use XLSXWriter;
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppMessageLogModel;

class WhatsAppServiceController extends BaseController
{
    /**
     * @var WhatsAppServiceEngine - WhatsAppService Engine
     */
    protected $whatsAppServiceEngine;

    /**
     * @var VendorSettingsEngine - VendorSettings  Engine
     */
    protected $vendorSettingsEngine;
    /**
     * @var WhatsAppTemplateEngine - WhatsApp TemplateEngine  Engine
     */
    protected $whatsAppTemplateEngine;

    /**
     * Constructor
     *
     * @param  WhatsAppServiceEngine  $whatsAppServiceEngine  - WhatsAppService Engine
     * @param  VendorSettingsEngine  $vendorSettingsEngine  - VendorSettings Engine
     * @param  WhatsAppTemplateEngine  $whatsAppTemplateEngine  - WhatsApp Template Engine
     *
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(
        WhatsAppServiceEngine $whatsAppServiceEngine,
        VendorSettingsEngine $vendorSettingsEngine,
        WhatsAppTemplateEngine $whatsAppTemplateEngine
    ) {
        $this->whatsAppServiceEngine = $whatsAppServiceEngine;
        $this->vendorSettingsEngine = $vendorSettingsEngine;
        $this->whatsAppTemplateEngine = $whatsAppTemplateEngine;
    }

    /**
     * Send Template Message View
     *
     * @param string $contactUid
     * @return view
     */
    public function sendTemplateMessageView($contactUid)
    {
        validateVendorAccess('messaging');
        $sendMessageResponseData = $this->whatsAppServiceEngine->sendMessageData($contactUid);
        // load the view
        return $this->loadView('whatsapp.template-send-message', $sendMessageResponseData->data());
    }

    /**
     * Template Based Send Message Process
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function sendTemplateMessageProcess(BaseRequestTwo $request)
    {
        validateVendorAccess('messaging');
        $request->validate([
            'template_uid' => 'required',
            'contact_uid' => 'required',
        ]);
        $processReaction = $this->whatsAppServiceEngine->processSendMessageForContact($request);

        // get back with response
        if ($processReaction->failed()) {
            return $this->processResponse($processReaction);
        }

        return $this->responseAction(
            $this->processResponse($processReaction),
            $this->redirectTo('vendor.chat_message.contact.view', [
                'contactUid' => $processReaction->data('contactUid'),
            ], [
                $processReaction->message(),
                'success',
            ])
        );
    }

    /**
     * Schedule Campaign
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function scheduleCampaign(BaseRequestTwo $request)
    {
        validateVendorAccess('manage_campaigns');
        $request->validate([
            'template_uid' => 'required',
            'contact_group' => 'required',
            'timezone' => 'required',
            'title' => 'required',
        ]);
        $processReaction = $this->whatsAppServiceEngine->processCampaignCreate($request);

        // get back with response
        if ($processReaction->failed()) {
            return $this->processResponse($processReaction);
        }

        return $this->responseAction(
            $this->processResponse($processReaction),
            $this->redirectTo('vendor.campaign.status.view', [
                'campaignUid' => $processReaction->data('campaignUid'),
            ], [
                $processReaction->message(),
                'success',
            ])
        );
    }

    /**
     * Create new Campaign View
     *
     * @return view
     */
    public function createNewCampaign()
    {
        validateVendorAccess('manage_campaigns');
        $campaignRequiredData = $this->whatsAppServiceEngine->campaignRequiredData();
        // load the view
        return $this->loadView('whatsapp.template-send-message', $campaignRequiredData->data());
    }

    /**
     * Check if has API feature enabled in plan or abort
     *
     * @param int $vendorId
     * @return void
     */
    protected function apiAccessAllowedOrAbort($vendorId = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        // check the feature limit
        $vendorPlanDetails = vendorPlanDetails('api_access', 0, $vendorId);
        abortIf(!$vendorPlanDetails['is_limit_available'], 401, 'API access is not available in your plan, please upgrade your subscription plan.');
    }

    /**
     * Send Chat Message
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function sendChatMessage(BaseRequestTwo $request)
    {
        validateVendorAccess('messaging');
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }
        $request->validate([
            'contact_uid' => 'required',
            'message_body' => 'required',
        ]);

        $processReaction = $this->whatsAppServiceEngine->processSendChatMessage($request);

        // get back with response
        if ($processReaction->failed()) {
            return $this->processResponse($processReaction);
        }
        return $this->processResponse($processReaction);
    }
    /**
     * Send Chat Message
     *
     * @param BaseRequestTwo $request
     * @since - 2.0.0
     *
     * @return json
     */
    public function apiSendChatMessage(BaseRequestTwo $request, $vendorUid)
    {
        $this->apiAccessAllowedOrAbort();
        validateVendorAccess('messaging');
        // check if account failed
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processApiResponse([
                'result' => 'failed',
                'message' => 'Please complete your WhatsApp Cloud API Setup first',
            ]);
        }
        // validate the inputs
        $request->validate([
            'phone_number' => 'required',
            'message_body' => 'required',
        ]);
        // send message
        $processReaction = $this->whatsAppServiceEngine->processSendChatMessage($request);
        // processed data
        $processedData = $processReaction->data();
        // get back the response
        return $this->processApiResponse($processReaction, [
            'log_uid' => $processedData['log_message']['_uid'] ?? null,
            'contact_uid' => $processedData['contact']['_uid'] ?? null,
            'phone_number' => $processedData['log_message']['contact_wa_id'] ?? null,
            'wamid' => $processedData['log_message']['wamid'] ?? null,
            'status' => $processedData['log_message']['status'] ?? null,
        ]);
    }

    /**
     * Send Chat Media Based Chat Message
     *
     * @param BaseRequestTwo $request
     * @since - 2.0.0
     *
     * @return json
     */
    public function apiSendMediaChatMessage(BaseRequestTwo $request)
    {
        $this->apiAccessAllowedOrAbort();
        validateVendorAccess('messaging');
        // check if account failed
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processApiResponse([
                'result' => 'failed',
                'message' => 'Please complete your WhatsApp Cloud API Setup first',
            ]);
        }
        // validate the inputs
        $request->validate([
            'phone_number' => 'required',
            'media_type' => [
                'required',
                Rule::in([
                    'image',
                    'video',
                    'document',
                    'audio',
                ])
            ],
            'media_url' => 'required|url',
        ]);
        // send message
        $processReaction = $this->whatsAppServiceEngine->processSendChatMessage($request, true);
        // processed data
        $processedData = $processReaction->data();
        // get back the response
        return $this->processApiResponse($processReaction, [
            'log_uid' => $processedData['log_message']['_uid'] ?? null,
            'contact_uid' => $processedData['contact']['_uid'] ?? null,
            'phone_number' => $processedData['log_message']['contact_wa_id'] ?? null,
            'wamid' => $processedData['log_message']['wamid'] ?? null,
            'status' => $processedData['log_message']['status'] ?? null,
        ]);
    }

    /**
    * Send Template Chat Message
    *
    * @param BaseRequestTwo $request
    * @since - 2.0.0
    *
    * @return json
    */
    public function apiSendTemplateChatMessage(BaseRequestTwo $request, $vendorUid)
    {
        $this->apiAccessAllowedOrAbort();
        validateVendorAccess('messaging');
        // check if account failed
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processApiResponse([
                'result' => 'failed',
                'message' => 'Please complete your WhatsApp Cloud API Setup first',
            ]);
        }
        // validate the inputs
        $request->validate([
            'phone_number' => 'required',
            'template_name' => 'required',
            'template_language' => 'required',
            'header_image' => 'sometimes|url',
            'header_video' => 'sometimes|url',
            'header_document' => 'sometimes|url',
        ]);
        // send message
        $processReaction = $this->whatsAppServiceEngine->processSendMessageForContact($request);
        // processed data
        $processedData = $processReaction->data();
        // get back the response
        return $this->processApiResponse($processReaction, [
            'log_uid' => $processedData['log_message']['_uid'] ?? null,
            'contact_uid' => $processedData['contactUid'] ?? null,
            'phone_number' => $processedData['log_message']['contact_wa_id'] ?? null,
            'wamid' => $processedData['log_message']['wamid'] ?? null,
            'status' => $processedData['log_message']['status'] ?? null,
        ]);
    }

    /**
     * Prepare Upload Media for the message
     *
     * @param BaseRequestTwo $request
     * @param string $mediaType
     * @return json
     */
    public function prepareSendMediaUploader(BaseRequestTwo $request, $mediaType = 'image')
    {
        if (! in_array($mediaType, [
            'image',
            'video',
            'audio',
            'document',
        ])) {
            return $this->processResponse(2, [
                __tr('Invalid media type'),
            ]);
        }

        return $this->processResponse(1, [], [
            'uploadTitle' => __tr('Select __mediaType__', [
                '__mediaType__' => $mediaType,
            ]),
            'mediaType' => $mediaType,
        ]);
    }

    /**
     * Send Chat Media Based Chat Message
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function sendChatMessageMedia(BaseRequestTwo $request)
    {
        validateVendorAccess('messaging');
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $request->validate([
            'contact_uid' => 'required',
            'media_type' => 'required',
            'uploaded_media_file_name' => 'required',
        ]);

        $processReaction = $this->whatsAppServiceEngine->processSendChatMessage($request, true);

        // get back with response
        if ($processReaction->failed()) {
            return $this->processResponse($processReaction);
        }

        return $this->processResponse($processReaction);
    }

    /**
     * Load Chat View
     *
     * @param string $contactUid
     * @return view
     */
    public function chatView($contactUid = null)
    {
        validateVendorAccess('messaging');
        if(!isVendorAdmin(getVendorId()) and hasVendorAccess('assigned_chats_only')) {
            request()->merge([
                'assigned' => 'to-me'
            ]);
        }
        $assigned = request()->assigned;
        $chatData = $this->whatsAppServiceEngine->chatData($contactUid, $assigned);

        if(request()->ajax()) {
            updateClientModels($chatData->data(), 'append');
            return $this->processResponse(1, [], [
                'currentlyAssignedUserUid' => $chatData->data('currentlyAssignedUserUid'),
            ]);
        }
        // load the view
        return $this->loadView('whatsapp.chat', $chatData->data());
    }

    /**
     * Get the contact chat data
     *
     * @param string $contactUid
     * @return json
     */
    public function getContactChatData($contactUid, $way = 'append')
    {
        validateVendorAccess('messaging');
        $processReaction = $this->whatsAppServiceEngine->contactChatData($contactUid);
        updateClientModels([
            'whatsappMessageLogs' => $processReaction->data('whatsappMessageLogs'),
        ], $way);

        return $this->processResponse($processReaction);
    }

    /**
     * Get the contacts list
     *
     * @param string $contactUid
     * @return void
     */
    public function getContactsData(BaseRequestTwo $request, $contactUid = null)
    {
        validateVendorAccess('messaging');
        $assigned = $request->assigned;
        $processReaction = $this->whatsAppServiceEngine->contactsData($contactUid, $assigned);
        updateClientModels($processReaction->data(), $request->way);

        return $this->processResponse($processReaction);
    }

    /**
     * Clear the user chat history on our system
     *
     * @param BaseRequestTwo $request
     * @param string $contactUid
     * @return void
     */
    public function clearChatHistory(BaseRequestTwo $request, $contactUid)
    {
        validateVendorAccess('messaging');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }

        $processReaction = $this->whatsAppServiceEngine->processClearChatHistory($contactUid);

        return $this->processResponse($processReaction);
    }

    /**
     * Change Template
     *
     * @param BaseRequestTwo $request
     * @return void
     */
    public function changeTemplate(BaseRequestTwo $request)
    {
        validateVendorAccess([
            'manage_campaigns',
            'messaging',
        ]);
        $request->validate([
            'template_selection' => [
                'required',
                'uuid',
            ],
        ]);
        // ask engine to process the request
        $processReaction = $this->whatsAppServiceEngine->processTemplateChange($request->get('template_selection'));
        if ($processReaction->success()) {
            return $this->responseAction(
                $this->processResponse($processReaction, [], [
                    '_uid' => $request->get('template_selection')
                ]),
                $this->replaceContent($processReaction->data('template'), '#lwTemplateStructureContainer')
            );
        }

        // get back with response
        return $this->processResponse($processReaction);
    }

    /**
     * Run Campaign Schedule mostly using Cron
     *
     * @param BaseRequestTwo $request
     * @param string $token - not in use for now
     * @return json
     */
    public function runCampaignSchedule(BaseRequestTwo $request, $token = '')
    {
        // ask engine to process the request
        $processReaction = $this->whatsAppServiceEngine->processCampaignSchedule();
        // get back with response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * WhatsApp Webhook for the notifications from WhatsApp
     *
     * @param BaseRequestTwo $request
     * @param string $vendorUid
     * @return void
     */
    public function webhook(BaseRequestTwo $request, $vendorUid)
    {
        // webhook verification process
        if ($request->isMethod('get')) {
            if ($request->has('hub_challenge') and $request->has('hub_verify_token')) {
                $verifyToken = sha1($vendorUid);
                if ($request->get('hub_verify_token') === $verifyToken) {
                    // if its base webhook call from service
                    if($vendorUid == 'service-whatsapp') {
                        return response($request->get('hub_challenge'));
                    }
                    $vendorId = getPublicVendorId($vendorUid);
                    if (!$vendorId) {
                        return false;
                    }
                    // update configuration for webhook
                    $this->vendorSettingsEngine->updateProcess('whatsapp_cloud_api_setup', [
                        'webhook_verified_at' => now()
                    ], $vendorId);
                    updateModelsViaVendorBroadcast($vendorUid, [
                        'isWebhookVerified' => true
                    ]);
                    return response($request->get('hub_challenge'));
                }
            }
            return response('Invalid request', 403);
        }
        // process the other update requests
        $this->whatsAppServiceEngine->processWebhook($request, $vendorUid);
        return response('done', 200);
    }

    /**
     * Get unread message count for vendor
     *
     * @return json
     */
    public function unreadCount()
    {
        validateVendorAccess([
            'manage_campaigns',
            'messaging',
        ]);
        $processReaction = $this->whatsAppServiceEngine->updateUnreadCount();
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Refresh WhatsApp Business Account Health Info
     *
     * @return json
     */
    public function getHealthStatus()
    {
        validateVendorAccess('administrative');
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $processReaction = $this->whatsAppServiceEngine->refreshHealthStatus();
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Refresh WhatsApp Business Account Health Info
     *
     * @return json
     */
    public function syncPhoneNumbers()
    {
        validateVendorAccess('administrative');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }

        $processReaction = $this->whatsAppServiceEngine->processSyncPhoneNumbers();
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Store the tokens and info
     *
     * @param BaseRequestTwo $request
     * @return array
     */
    public function embeddedSignUpProcess(BaseRequestTwo $request)
    {
        validateVendorAccess('administrative');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $request->validate([
            'request_code' => [
                'required'
            ],
            'waba_id' => [
                'required',
                'numeric'
            ],
            'phone_number_id' => [
                'required',
                'numeric'
            ],
        ]);
        $processReaction = $this->whatsAppServiceEngine->setupWhatsAppEmbeddedSignUpProcess($request);
        if($processReaction->success()) {
            // sync templates
            $this->whatsAppTemplateEngine->processSyncTemplates();
            return $this->processResponse(21, [], [
                'reloadPage' => true
            ], true);
        }
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Disconnect Base Webhook
     *
     * @return json
     */
    public function disconnectWebhook()
    {
        validateVendorAccess('administrative');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $processReaction = $this->whatsAppServiceEngine->processDisconnectWebhook();
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Disconnect Base Webhook
     *
     * @return json
     */
    public function disconnectAccount()
    {
        validateVendorAccess('administrative');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $processReaction = $this->whatsAppServiceEngine->processDisconnectAccount();
        if($processReaction->success()) {
            return $this->processResponse(21, [], [
                'reloadPage' => true,
                'show_message' => true,
                'messageType' => 'success',
            ], true);
        }
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
     * Connect Base Webhook
     *
     * @return json
     */
    public function connectWebhook()
    {
        validateVendorAccess('administrative');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $processReaction = $this->whatsAppServiceEngine->processConnectWebhook();
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Requeue failed messages for processing
     *
     * @param BaseRequestTwo $request
     * @param string $campaignUid  campaign uid
     * @return json
     */
    public function requeueCampaignFailedMessages(BaseRequestTwo $request, $campaignUid)
    {
        validateVendorAccess('manage_campaigns');
        $processReaction = $this->whatsAppServiceEngine->processRequeueFailedMessages($request, $campaignUid);
        // get back with response
        if ($processReaction->success()) {
            return $this->processResponse($processReaction, [], [
                // reload datatable on success
                'reloadDatatableId' => '#lwCampaignQueueLog'
            ]);
        }
        return $this->processResponse($processReaction);
    }

    /**
     * Get Business Profile
     *
     * @param int $phoneNumberId
     * @return json
     */
    function getBusinessProfile($phoneNumberId) {
        validateVendorAccess('administrative');
        // ask engine to process the request
        $processReaction = $this->whatsAppServiceEngine->requestBusinessProfile($phoneNumberId);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }
    function updateBusinessProfile(BaseRequestTwo $request) {
        validateVendorAccess('administrative');
        $request->validate([
            'address' => [
                'nullable',
                'max:256',
            ],
            'description' => [
                'nullable',
                'max:256',
            ],
            'about' => [
                'nullable',
                'max:139',
            ],
            'about' => [
                'nullable',
                'max:139',
            ],
            'email' => [
                'nullable',
                'email',
                'max:128',
            ],
        ]);
        // ask engine to process the request
        $processReaction = $this->whatsAppServiceEngine->requestUpdateBusinessProfile($request);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }
        /**
     * Export chats report to .xlsx
     *
     * @return file .xlsx
     */
    public function exportChats()
{
    $tempFile = tempnam(sys_get_temp_dir(), "temp.xlsx");
    $vendorId = getVendorId();
    
    // First, get all unique contacts with their basic info
    $contacts = WhatsAppMessageLogModel::select(
        'contacts._id as contact_id',
        'contacts.first_name as First Name',
        'contacts.last_name as Last Name',
        'whatsapp_message_logs.contact_wa_id as Contact WA ID'
    )
    ->join('contacts', 'whatsapp_message_logs.contacts__id', '=', 'contacts._id')
    ->where('whatsapp_message_logs.vendors__id', $vendorId)
    ->groupBy('contacts._id', 'contacts.first_name', 'contacts.last_name', 'whatsapp_message_logs.contact_wa_id')
    ->get();
    
    // Determine the maximum number of messages any contact has
    $maxMessages = 0;
    $groupedData = [];
    
    // Track maximum content length for each column to set column widths later
    $maxLengths = [
        'First Name' => strlen('First Name'),
        'Last Name' => strlen('Last Name'),
        'Contact WA ID' => strlen('Contact WA ID'),
        'Direction' => strlen('Direction'),
        'Content' => 0
    ];
    
    foreach ($contacts as $contact) {
        $messages = WhatsAppMessageLogModel::select(
            'whatsapp_message_logs._id as Chat ID',
            'whatsapp_message_logs.message as Message',
            'whatsapp_message_logs.is_incoming_message as Incoming',
            'whatsapp_message_logs.messaged_at as Sent At',
            'whatsapp_message_logs.__data as Data' // Added __data column selection
        )
        ->where('whatsapp_message_logs.contacts__id', $contact->contact_id)
        ->where('whatsapp_message_logs.vendors__id', $vendorId)
        ->orderBy('whatsapp_message_logs.messaged_at', 'desc')
        ->get();
        
        // Update max lengths for auto-width calculations
        $maxLengths['First Name'] = max($maxLengths['First Name'], strlen($contact['First Name']));
        $maxLengths['Last Name'] = max($maxLengths['Last Name'], strlen($contact['Last Name']));
        $maxLengths['Contact WA ID'] = max($maxLengths['Contact WA ID'], strlen($contact['Contact WA ID']));
        
        foreach ($messages as $message) {
            // Process the message content
            $messageContent = $message['Message'];
            
            // Check if this is an outgoing message with empty content
            if (!$message['Incoming'] && (empty($messageContent) || trim($messageContent) === '')) {
                // Try to get template information from __data
                if (!empty($message['Data'])) {
                    try {
                        $messageData = json_decode($message['Data'], true);
                        
                        // Check if template information exists in the data
                        if (isset($messageData['template_proforma']['name'])) {
                            $templateName = $messageData['template_proforma']['name'];
                            $messageContent = "[Template: {$templateName}]";
                        }
                    } catch (\Exception $e) {
                        // In case of JSON parsing error, use a fallback message
                        $messageContent = "[Template data unavailable]";
                    }
                } else {
                    $messageContent = "[No content]";
                }
                
                // Update the message content
                $message['Message'] = $messageContent;
            }
            
            $maxLengths['Direction'] = max($maxLengths['Direction'], strlen($message['Incoming'] ? 'Incoming' : 'Outgoing'));
            $maxLengths['Content'] = max($maxLengths['Content'], min(strlen($message['Message']), 100)); // Limit to reasonable size
        }
        
        $messageCount = $messages->count();
        if ($messageCount > $maxMessages) {
            $maxMessages = $messageCount;
        }
        
        $groupedData[] = [
            'Name' => $contact['First Name']." ".$contact['Last Name'],
            'Contact WA ID' => $contact['Contact WA ID'],
            'Messages' => $messages
        ];
    }
    
    // Initialize XLSXWriter
    $writer = new XLSXWriter();
    
    // Define styles
    $header_style = [
        'font-style' => 'bold',
        'fill' => '#4472C4',
        'color' => '#FFFFFF',
        'halign' => 'center',
        'border' => 'left,right,top,bottom',
    ];
    
    $contact_style = [
        'fill' => '#E2EFDA',
        'border' => 'left,right,top,bottom',
        'halign' => 'left',
        'valign' => 'center',
    ];
    
    // Function to generate random pastel color for message blocks
    $generatePastelColor = function() {
        $red = mt_rand(180, 240);
        $green = mt_rand(180, 240);
        $blue = mt_rand(180, 240);
        return sprintf('#%02X%02X%02X', $red, $green, $blue);
    };
    
    // Define column formats based on content types
    $formats = [
        'Name' => 'string',
        'WhatsApp Number' => 'string'
    ];
    
    // Add formats for message columns - now only Direction and Content
    for ($i = 1; $i <= $maxMessages; $i++) {
        $formats["Type $i"] = 'string';
        $formats["Message $i"] = 'string';
    }
    
    // Prepare column widths array
    $columnWidths = [
        25, // Name
        20, // Contact WA ID
    ];
    
    // Add widths for message columns
    for ($i = 1; $i <= $maxMessages; $i++) {
        $columnWidths[] = 10;  // Direction
        $columnWidths[] = 30;  // Content - increased width for all content cells
    }
    
    // Set sheet metadata with column widths
    $sheet_name = 'Chats';
    $writer->writeSheetHeader(
        $sheet_name, 
        $formats, 
        [
            'suppress_row' => false,
            'widths' => $columnWidths
        ] + $header_style
    );
    
    // Write data rows
    foreach ($groupedData as $row) {
        $rowData = [
            $row['Name'],
            $row['Contact WA ID']
        ];
        
        // Setup row styles
        $rowStyles = [
            0 => $contact_style,
            1 => $contact_style,
        ];
        
        // Add messages
        $messages = $row['Messages'];
        $colIndex = 3;
        
        for ($i = 0; $i < $maxMessages; $i++) {
            // Generate a random pastel color for this message block
            $messageColor = $generatePastelColor();
            $messageStyle = [
                'fill' => $messageColor,
                'border' => 'left,right,top,bottom',
                'valign' => 'center',
            ];
            
            $contentStyle = [
                'fill' => $messageColor,
                'border' => 'left,right,top,bottom',
                'valign' => 'top',
                'wrap_text' => true
            ];
            
            if (isset($messages[$i])) {
                $message = $messages[$i];
                $direction = $message['Incoming'] ? 'Incoming' : 'Outgoing';
                
                $rowData[] = $direction;
                $rowData[] = $message['Message'];
                
                // Apply styles to these cells
                $rowStyles[$colIndex] = $messageStyle;
                $rowStyles[$colIndex + 1] = $contentStyle; // Content gets wrap text
            } else {
                // Empty cells
                $rowData[] = '';
                $rowData[] = '';
                
                // Still apply consistent styling
                $rowStyles[$colIndex] = $messageStyle;
                $rowStyles[$colIndex + 1] = $contentStyle;
            }
            
            $colIndex += 2; // Incrementing by 2 for each message
        }
        
        // Write row with all styles
        if (method_exists($writer, 'writeSheetRowWithStyles')) {
            $writer->writeSheetRowWithStyles($sheet_name, $rowData, $rowStyles);
        } else {
            // Fallback if custom styling per cell isn't supported in your version
            $writer->writeSheetRow($sheet_name, $rowData);
        }
    }
    
    // Save the file
    $writer->writeToFile($tempFile);
     // Define file name
     $fileName = 'WhatsApp_Chat_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    return response()->download($tempFile, $fileName, [
        'Content-Transfer-Encoding: binary',
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Cache-Control' => 'must-revalidate',
        'Pragma' => 'public',
    ])->deleteFileAfterSend();
}

 
}
