<?php
/**
* WhatsAppServiceEngine.php - Main component file
*
* This file is part of the WhatsAppService component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\Client\Pool;
use App\Yantrana\Base\BaseEngine;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Events\VendorChannelBroadcast;
use App\Jobs\ProcessCampaignMessagesJob;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\RequestException;
use App\Yantrana\Components\Media\MediaEngine;
use Illuminate\Http\Client\ConnectionException;
use App\Yantrana\Components\Vendor\VendorSettingsEngine;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Components\Configuration\ConfigurationEngine;
use App\Yantrana\Components\Contact\Repositories\LabelRepository;
use App\Yantrana\Components\Contact\Repositories\ContactRepository;
use App\Yantrana\Components\WhatsAppService\Services\OpenAiService;
use App\Yantrana\Components\BotReply\Repositories\BotReplyRepository;
use App\Yantrana\Components\BotReply\Repositories\BotFlowRepository;
use App\Yantrana\Components\BotReply\Models\PseudoBotReply;
use App\Yantrana\Components\Campaign\Repositories\CampaignRepository;
use App\Yantrana\Components\Contact\Repositories\ContactGroupRepository;
use App\Yantrana\Components\Contact\Repositories\GroupContactRepository;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppApiService;
use App\Yantrana\Components\Contact\Repositories\ContactCustomFieldRepository;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppConnectApiService;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;
use App\Yantrana\Components\WhatsAppService\Interfaces\WhatsAppServiceEngineInterface;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppMessageLogRepository;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppMessageQueueRepository;
use App\Models\UserActiveFlow;
use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\BotReply\Services\FlowIntegrationService;

class WhatsAppServiceEngine extends BaseEngine implements WhatsAppServiceEngineInterface
{
    /**
     * @var ContactRepository - Contact Repository
     */
    protected $contactRepository;

    /**
     * @var ContactGroupRepository - ContactGroup Repository
     */
    protected $contactGroupRepository;

    /**
     * @var GroupContactRepository - ContactGroup Repository
     */
    protected $groupContactRepository;

    /**
     * @var WhatsAppTemplateRepository - WhatsApp Template Repository
     */
    protected $whatsAppTemplateRepository;

    /**
     * @var WhatsAppApiService - WhatsApp API Service
     */
    protected $whatsAppApiService;

    /**
     * @var MediaEngine - Media Engine
     */
    protected $mediaEngine;

    /**
     * @var WhatsAppMessageLogRepository - Status repository
     */
    protected $whatsAppMessageLogRepository;

    /**
     * @var WhatsAppMessageQueueRepository - WhatsApp Message Queue repository
     */
    protected $whatsAppMessageQueueRepository;
    /**
     * @var CampaignRepository - Campaign repository
     */
    protected $campaignRepository;

    /**
     * @var BotReplyRepository - Bot Reply repository
     */
    protected $botReplyRepository;

    /**
     * @var BotFlowRepository - Bot Flow repository
     */
    protected $botFlowRepository;

    /**
     * @var VendorSettingsEngine - Vendor Settings Engine
     */
    protected $vendorSettingsEngine;

    /**
     * @var UserRepository - UserRepository
     */
    protected $userRepository;

    /**
     * @var ConfigurationEngine - configurationEngine
     */
    protected $configurationEngine;

    /**
     * @var ContactCustomFieldRepository - ContactGroup Repository
     */
    protected $contactCustomFieldRepository;

    /**
     * @var WhatsAppConnectApiService - WhatsApp Connect Service
     */
    protected $whatsAppConnectApiService;

    /**
     * @var LabelRepository - Label Repository
     */
    protected $labelRepository;

    /**
     * Constructor
     *
     * @param  ContactRepository  $contactRepository  - Contact Repository
     * @param  ContactGroupRepository  $contactGroupRepository  - ContactGroup Repository
     * @param  GroupContactRepository  $groupContactRepository  - Group Contacts Repository
     * @param  WhatsAppTemplateRepository  $whatsAppTemplateRepository  - WhatsApp Templates Repository
     * @param  WhatsAppApiService  $whatsAppApiService  - WhatsApp API Service
     * @param  WhatsAppMessageQueueRepository  $whatsAppMessageQueueRepository  - WhatsApp Message Queue
     * @param  CampaignRepository  $campaignRepository  - Campaign repository
     * @param  BotReplyRepository  $botReplyRepository  - Bot Reply repository
     * @param  BotFlowRepository  $botFlowRepository  - Bot Flow repository
     * @param  VendorSettingsEngine  $vendorSettingsEngine  - Configuration Engine
     * @param  UserRepository  $userRepository  - Users Repository
     * @param  ConfigurationEngine  $configurationEngine  - Configuration Engine
     * @param  WhatsAppConnectApiService  $whatsAppConnectApiService  - WhatsApp Connect Service
     * @param  ContactCustomFieldRepository  $contactCustomFieldRepository  - Contacts Custom  Fields Repository
     * @param  LabelRepository  $labelRepository  - Label Repository
     *
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(
        ContactRepository $contactRepository,
        ContactGroupRepository $contactGroupRepository,
        GroupContactRepository $groupContactRepository,
        WhatsAppTemplateRepository $whatsAppTemplateRepository,
        WhatsAppApiService $whatsAppApiService,
        MediaEngine $mediaEngine,
        WhatsAppMessageLogRepository $whatsAppMessageLogRepository,
        WhatsAppMessageQueueRepository $whatsAppMessageQueueRepository,
        CampaignRepository $campaignRepository,
        BotReplyRepository $botReplyRepository,
        BotFlowRepository $botFlowRepository,
        VendorSettingsEngine $vendorSettingsEngine,
        UserRepository $userRepository,
        ConfigurationEngine $configurationEngine,
        WhatsAppConnectApiService $whatsAppConnectApiService,
        ContactCustomFieldRepository $contactCustomFieldRepository,
        LabelRepository $labelRepository
    ) {
        $this->contactRepository = $contactRepository;
        $this->contactGroupRepository = $contactGroupRepository;
        $this->groupContactRepository = $groupContactRepository;
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
        $this->whatsAppApiService = $whatsAppApiService;
        $this->mediaEngine = $mediaEngine;
        $this->whatsAppMessageLogRepository = $whatsAppMessageLogRepository;
        $this->whatsAppMessageQueueRepository = $whatsAppMessageQueueRepository;
        $this->campaignRepository = $campaignRepository;
        $this->botReplyRepository = $botReplyRepository;
        $this->botFlowRepository = $botFlowRepository;
        $this->vendorSettingsEngine = $vendorSettingsEngine;
        $this->userRepository = $userRepository;
        $this->configurationEngine = $configurationEngine;
        $this->whatsAppConnectApiService = $whatsAppConnectApiService;
        $this->contactCustomFieldRepository = $contactCustomFieldRepository;
        $this->labelRepository = $labelRepository;
    }

    /**
     * Get Contact Info
     *
     * @param  string  $contactUid
     * @return EngineResponse
     */
    public function sendMessageData($contactUid)
    {
        $vendorId = getVendorId();
        $contact = $this->contactRepository->getVendorContact($contactUid, $vendorId);
        abortIf(__isEmpty($contact));
        $whatsAppApprovedTemplates = $this->whatsAppTemplateRepository->getApprovedTemplatesByNewest();

        return $this->engineSuccessResponse([
            'contact' => $contact,
            'whatsAppTemplates' => $whatsAppApprovedTemplates,
            'template' => '',
            'templatePreview' => '',
        ]);
    }

    /**
     * Get Contact Info
     *
     * @param  string  $contactUid
     * @return EngineResponse
     */
    public function campaignRequiredData()
    {
        $vendorId = getVendorId();
        // templates
        $whatsAppApprovedTemplates = $this->whatsAppTemplateRepository->getApprovedTemplatesByNewest();
        // contact groups
        $vendorContactGroups = $this->contactGroupRepository->getActiveGroups($vendorId);

        return $this->engineSuccessResponse([
            'contact' => null,
            'whatsAppTemplates' => $whatsAppApprovedTemplates,
            'vendorContactGroups' => $vendorContactGroups,
            'template' => '',
            'templatePreview' => '',
        ]);
    }

    /**
     * Process the template change
     *
     * @param  string|int  $whatsAppTemplateId
     * @return EngineResponse
     */
    public function processTemplateChange($whatsAppTemplateId)
    {
        $preparedTemplateData = $this->prepareTemplate($whatsAppTemplateId);

        return $this->engineSuccessResponse([
            'template' => $preparedTemplateData['template'],
            'templateData' => $preparedTemplateData['templateData'],
        ]);
    }

    /**
     * Prepare Template with required parameters
     *
     * @param  string|int  $whatsAppTemplateId
     * @param  array  $options
     * @return array
     */
    protected function prepareTemplate($whatsAppTemplateId, array $options = [])
    {
        $options = array_merge([
            'templateComponents' => null,
        ], $options);
        // useful for message
        if ($whatsAppTemplateId == 'for_message') {
            $whatsAppTemplate = null;
            $templateComponents = &$options['templateComponents'];
        } else {
            $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt($whatsAppTemplateId);
            abortIf(__isEmpty($whatsAppTemplate), null, __tr('Template not found'));
            $templateComponents = Arr::get($whatsAppTemplate->toArray(), '__data.template.components');
        }

        $bodyComponentText = '';
        $headerComponentText = '';
        $componentButtonText = '';
        $buttonItems = [];
        $headerParameters = [];
        $headerFormat = null;
        $btnIndex = 0;
        $buttonParameters = [];
        $carouselCards = [];
        $isCarouselTemplate = false;
        foreach ($templateComponents as $templateComponent) {
            if ($templateComponent['type'] == 'HEADER') {
                $headerFormat = $templateComponent['format'];
                if ($templateComponent['format'] == 'TEXT') {
                    $headerComponentText = $templateComponent['text'];
                }
            } elseif ($templateComponent['type'] == 'BODY') {
                $bodyComponentText = $templateComponent['text'];
            } elseif ($templateComponent['type'] == 'BUTTONS') {
                foreach ($templateComponent['buttons'] as $templateComponentButton) {
                    if ($templateComponentButton['type'] == 'URL' and (Str::contains($templateComponentButton['url'], '{{1}}'))) {
                        $buttonItems[] = [
                            'type' => $templateComponentButton['type'],
                            'url' => $templateComponentButton['url'],
                            'text' => $templateComponentButton['text'],
                        ];
                        $buttonParameters[] = "button_$btnIndex";
                    } elseif ($templateComponentButton['type'] == 'COPY_CODE') {
                        $buttonItems['COPY_CODE'] = [
                            'type' => $templateComponentButton['type'],
                            'text' => $templateComponentButton['text'],
                        ];
                    }
                    $btnIndex++;
                }
            } elseif ($templateComponent['type'] == 'CAROUSEL') {
                $isCarouselTemplate = true;
                $carouselCards = $templateComponent['cards'];
            }
        }
        // Regular expression to match {{number}}
        $pattern = '/{{\d+}}/';
        // Find matches
        preg_match_all($pattern, $headerComponentText, $headerVariableMatches);
        // $templateParameters = $matches[0]; // will contain all matched patterns
        $headerParameters = array_map(function ($item) {
            return 'header_field_' . strtr($item, [
                '{{' => '',
                '}}' => '',
            ]);
        }, $headerVariableMatches[0]); // will contain all matched patterns
        // Find matches
        preg_match_all($pattern, $bodyComponentText, $matches);
        // $templateParameters = $matches[0]; // will contain all matched patterns
        $bodyParameters = array_map(function ($item) {
            return 'field_' . strtr($item, [
                '{{' => '',
                '}}' => '',
            ]);
        }, $matches[0]); // will contain all matched patterns

        $templateDataPrepared = [
            'buttonItems' => $buttonItems,
            'templateComponents' => $templateComponents,
            'headerParameters' => $headerParameters,
            'buttonParameters' => $buttonParameters,
            'bodyParameters' => $bodyParameters,
            // 'buttonParameters' => $buttonParameters,
            'template' => $whatsAppTemplate,
            'headerFormat' => $headerFormat,
            // for preview
            'bodyComponentText' => $bodyComponentText,
            'contactDataMaps' => getContactDataMaps(),
            // carousel support
            'isCarouselTemplate' => $isCarouselTemplate,
            'carouselCards' => $carouselCards,
        ];

        if ($options['templateComponents']) {
            return $templateDataPrepared;
        }

        return [
            'template' => view('whatsapp-service.message-preparation', $templateDataPrepared)->render(),
            'templateData' => $templateDataPrepared,
        ];
    }

    /**
     * Send message for selected contact
     *
     * @param  BaseRequestTwo  $request
     * @return EngineResponse
     */
    public function processSendMessageForContact($request)
    {
        $contact = $this->contactRepository->getVendorContact($request->get('contact_uid'));
        if (__isEmpty($contact)) {
            if (isExternalApiRequest()) {
                $contact = $this->createAContactForApiRequest($request);
            } else {
                return $this->engineFailedResponse([], __tr('Requested contact does not found'));
            }
        }

        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $contact->vendors__id);
        if (!$vendorPlanDetails->hasActivePlan()) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }

        return $this->sendTemplateMessageProcess($request, $contact);
    }

    /**
     * Create contact if does not exist
     *
     * @param BaseRequestTwo $request
     * @return void
     */
    protected function createAContactForApiRequest($request)
    {
        $vendorId = getVendorId();
        // check the feature limit
        $vendorPlanDetails = vendorPlanDetails('contacts', $this->contactRepository->countIt([
            'vendors__id' => $vendorId
        ]), $vendorId);

        abortIf(!$vendorPlanDetails['is_limit_available'], null, $vendorPlanDetails['message']);

        $request->validate([
            'contact.first_name' => [
                'nullable',
                'max:150',
            ],
            'contact.last_name' => [
                'nullable',
                'max:150',
            ],
            'contact.country' => 'nullable',
            'contact.language_code' => 'nullable|alpha_dash',
            "phone_number" => [
                'required',
                'numeric',
                'min_digits:9',
                'min:1',
            ],
            'contact.email' => 'nullable|email',
        ]);

        $dataForContact = Arr::only(($request->contact ?? []), [
            'first_name',
            'last_name',
            'language_code',
            'email',
            'country',
        ]);
        // abortIf(str_starts_with($request->phone_number, '0') or str_starts_with($request->phone_number, '+'), null, 'phone number should be numeric value without prefixing 0 or +');

        // create contact
        if ($contactCreated = $this->contactRepository->storeContact([
            'first_name' => $dataForContact['first_name'] ?? '',
            'last_name' => $dataForContact['last_name'] ?? '',
            'email' => $dataForContact['email'] ?? '',
            'language_code' => $dataForContact['language_code'] ?? '',
            'phone_number' => cleanDisplayPhoneNumber($request->phone_number),
            'country' => getCountryIdByName($dataForContact['country'] ?? null),
        ], $vendorId)) {
            // prepare group ids needs to be assign to the contact
            $contactGroupsTitles = array_filter(array_unique(explode(',', $request->contact['groups'] ?? '') ?? []));
            if (!empty($contactGroupsTitles)) {
                // prepare group titles needs to be assign to the contact
                $groupsToBeAdded = $this->contactGroupRepository->fetchItAll($contactGroupsTitles, [], 'title', [
                    'where' => [
                        'vendors__id' => $vendorId
                    ]
                ]);
                $groupsToBeCreatedTitles = array_diff($contactGroupsTitles, $groupsToBeAdded->pluck('title')->toArray());
                $groupsToBeCreated = [];
                if (!empty($groupsToBeCreatedTitles)) {
                    foreach ($groupsToBeCreatedTitles as $groupsToBeCreatedTitle) {
                        if (strlen($groupsToBeCreatedTitle) > 255) {
                            abortIf(strlen($groupsToBeCreatedTitle) > 1, null, __tr('Group title should not be greater than 255 characters'));
                        }
                        $groupsToBeCreated[] = [
                            'title' => $groupsToBeCreatedTitle,
                            'vendors__id' => $vendorId,
                            'status' => 1,
                        ];
                    }
                    if (!empty($groupsToBeCreated)) {
                        $newlyCreatedGroupIds = $this->contactGroupRepository->storeItAll($groupsToBeCreated, true);
                        if (!empty($newlyCreatedGroupIds)) {
                            $newlyCreatedGroups = $this->contactGroupRepository->fetchItAll(array_values($newlyCreatedGroupIds));
                            if (!__isEmpty($groupsToBeAdded)) {
                                $groupsToBeAdded->merge($newlyCreatedGroups);
                            }
                        }
                    }
                }
                $assignGroups = [];
                // prepare to assign if needed
                if (! empty($groupsToBeAdded)) {
                    foreach ($groupsToBeAdded as $groupToBeAdded) {
                        if ($groupToBeAdded->vendors__id != $vendorId) {
                            continue;
                        }
                        $assignGroups[] = [
                            'contact_groups__id' => $groupToBeAdded->_id,
                            'contacts__id' => $contactCreated->_id,
                        ];
                    }
                    $this->groupContactRepository->storeItAll($assignGroups);
                }
            }
        }

        return $contactCreated;
    }

    /**
     * get Current Billing Cycle
     *
     * @param string $subscriptionStartDate
     * @return array
     */
    public function getCurrentBillingCycleDates($subscriptionStartDate)
    {
        $today = Carbon::now();
        $startOfMonth = new Carbon($subscriptionStartDate);
        // Adjust the start date to the current period
        $startOfMonth->year($today->year)->month($today->month);
        if ($today->day < $startOfMonth->day) {
            // If today is before the subscription day this month, start from last month
            $startOfMonth->subMonth();
        }
        $endOfMonth = (clone $startOfMonth)->addMonth()->subDay(); // End of this billing cycle
        return [
            'start' => $startOfMonth->startOfDay(), // Ensure time part is zeroed out
            'end' => $endOfMonth->endOfDay(), // Include the entire last day
        ];
    }

    /**
     * Process the message for Campaign creation
     *
     * @param  Request  $request
     * @return EngineResponse
     */
    public function processCampaignCreate($request)
    {
        $vendorId = getVendorId();
        // check the feature limit
        $subscription = getVendorCurrentActiveSubscription($vendorId);
        $currentBillingCycle = $this->getCurrentBillingCycleDates($subscription->created_at ?? getUserAuthInfo('vendor_created_at'));
        $vendorPlanDetails = vendorPlanDetails('campaigns', $this->campaignRepository->countIt([
            'vendors__id' => $vendorId,
            [
                'created_at', '>=', $currentBillingCycle['start'],
            ], [
                'created_at', '<=', $currentBillingCycle['end'],
            ]
        ]), $vendorId);

        if (!$vendorPlanDetails['is_limit_available']) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }

        $scheduleAt = $request->get('schedule_at');
        $timezone = $request->get('timezone');
        // if seconds missing, complete required date time format
        if (strlen($scheduleAt) == 16) {
            $scheduleAt = $scheduleAt . ':00';
        }
        if ($scheduleAt) {
            try {
                $rawTime = Carbon::createFromFormat('Y-m-d\TH:i:s', $scheduleAt, $timezone);
                $scheduleAt = $rawTime->setTimezone('UTC');
            } catch (\Throwable $th) {
                return $this->engineFailedResponse([], __tr('Failed to recognize the datetime, please reload and try again.'));
            }
        } else {
            $scheduleAt = now();
        }
        $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt($request->template_uid);
        abortIf(__isEmpty($whatsAppTemplate), null, __tr('Template not found in the system'));
        $contactGroupId = $request->contact_group;
        $restrictByTemplateContactLanguage = $request->restrict_by_templated_contact_language == 'on';
        $contactsWhereClause = [
            'vendors__id' => $vendorId,
        ];
        $isOnlyForOptedContacts = false;
        //if its Marketing campaign and user is opted out don't process it further
        if (($whatsAppTemplate->category == 'MARKETING')) {
            $contactsWhereClause['whatsapp_opt_out'] = null;
            $isOnlyForOptedContacts = true;
        }

        if ($restrictByTemplateContactLanguage) {
            $contactsWhereClause['language_code'] = $whatsAppTemplate->language;
        }
        $groupContactIds = [];
        $phoneNumbers = [];
        
        // Process phone numbers if provided
        if ($request->has('phone_numbers') && !empty($request->phone_numbers)) {
            $phoneNumbers = array_map('trim', explode(',', $request->phone_numbers));
            $phoneNumbers = array_filter($phoneNumbers, function($number) {
                return !empty($number);
            });
            
            // Validate phone numbers
            foreach ($phoneNumbers as $number) {
                if (!preg_match('/^[0-9]{10,15}$/', $number)) {
                    return $this->engineFailedResponse([], __tr('Invalid phone number format: ' . $number));
                }
            }
        }
        
        // if not all contacts and no phone numbers provided
        if ($contactGroupId != 'all_contacts' && empty($phoneNumbers)) {
            $contactGroup = $this->contactGroupRepository->fetchIt([
                '_id' => $contactGroupId,
                'vendors__id' => $vendorId,
            ]);
            if (__isEmpty($contactGroup)) {
                return $this->engineFailedResponse([], __tr('Invalid Group'));
            }
            $groupContacts = $this->groupContactRepository->fetchItAll([
                'contact_groups__id' => $contactGroupId
            ]);
            if (__isEmpty($groupContacts)) {
                return $this->engineFailedResponse([], __tr('Group Contact does not found'));
            }
            $groupContactIds = $groupContacts->pluck('contacts__id')->toArray();
        }
        
        // Get contacts count from groups
        $totalContacts = $this->contactRepository->countContactsForCampaign($contactsWhereClause, $groupContactIds);
        
        // Add phone numbers count
        $totalContacts += count($phoneNumbers);
        
        if ($totalContacts === 0) {
            return $this->engineFailedResponse([], __tr('No contacts found. Please select a group or enter phone numbers.'));
        }
        // demo account restrictions
        if (isDemo() and ($totalContacts > 3)) {
            return $this->engineFailedResponse([], __tr('DEMO LIMIT: For the demo purposes you can not send campaign messages to more than 3 contacts.'));
        }
        $testContactUid = getVendorSettings('test_recipient_contact');
        if (!$testContactUid) {
            return $this->engineFailedResponse([], __tr('Test Contact missing, You need to set the Test Contact first, do it under the WhatsApp Settings'));
        }
        $contact = $this->contactRepository->getVendorContact($testContactUid);
        if (__isEmpty($contact)) {
            return $this->engineFailedResponse([], __tr('Test contact does not found'));
        }
        // send test message
        $isTestMessageProcessed = $this->sendTemplateMessageProcess($request, $contact, false, null, $vendorId, $whatsAppTemplate);
        if ($isTestMessageProcessed->failed()) {
            return $this->engineFailedResponse([], __tr('Failed to send test message'));
        }
        // remove test message log entry
        $this->whatsAppMessageLogRepository->deleteIt($isTestMessageProcessed->data('message_log_id'));

        // create campaign data
        $campaignData = [
            'title' => $request->title,
            'scheduled_at' => $scheduleAt,
            'vendors__id' => $vendorId,
            'whatsapp_templates__id' => $whatsAppTemplate->_id,
            '__data' => [
                'total_contacts' => $totalContacts,
                'is_all_contacts' => $contactGroupId == 'all_contacts',
                'is_for_template_language_only' => $restrictByTemplateContactLanguage,
                'selected_groups' => $contactGroupId == 'all_contacts' ? [] : [$contactGroupId],
                'phone_numbers' => $phoneNumbers,
                'is_only_for_opted_contacts' => $isOnlyForOptedContacts,
                'group_info' => [
                    'title' => $contactGroup && isset($contactGroup->title) ? $contactGroup->title : null,
                    'description' => $contactGroup && isset($contactGroup->description) ? $contactGroup->description : null,
                    'total_group_contacts' => $totalContacts - count($phoneNumbers)
                ]
            ]
        ];
        
        // Store the campaign
        $campaign = $this->campaignRepository->storeIt($campaignData);
        $isSucceed = false;
        $this->contactRepository->getContactsForCampaignInChunks($contactsWhereClause, $groupContactIds, function (Collection $contacts) use (&$request, &$isTestMessageProcessed, &$vendorId, &$whatsAppTemplate, &$scheduleAt, &$campaign, &$isSucceed) {
            $queueData = [];
            foreach ($contacts as $contact) {
                // if number is missing don't process it further
                if (!$contact->wa_id) {
                    continue;
                }
                $templateMessageSentProcess = $this->sendTemplateMessageProcess($request, $contact, true, $campaign->_id, $vendorId, $whatsAppTemplate, $isTestMessageProcessed->data('inputs'));
                // large contacts simulation
                // for ($i=0; $i < 2000; $i++) {
                $queueData[] = [
                    'vendors__id' => $vendorId,
                    'status' => 1, // queue
                    'scheduled_at' => $scheduleAt,
                    'phone_with_country_code' => $contact->wa_id,
                    'campaigns__id' => $campaign->_id,
                    'contacts__id' => $contact->_id,
                    '__data' => [
                        'contact_data' => [
                            '_id' => $contact->_id,
                            '_uid' => $contact->_uid,
                            'first_name' => $contact->first_name,
                            'last_name' => $contact->last_name,
                            'countries__id' => $contact->countries__id,
                        ],
                        'campaign_data' => $templateMessageSentProcess->data()
                    ]
                ];
                // }
            }
            if ($this->whatsAppMessageQueueRepository->storeItAll($queueData)) {
                $isSucceed = true;
            }
        });

        if ($isSucceed) {
            if (getAppSettings('enable_queue_jobs_for_campaigns')) {
                if ($totalContacts) {
                    $numberOfJobs = ceil($totalContacts / getAppSettings('cron_process_messages_per_lot'));
                    $numberOfJobs = $numberOfJobs + ceil($numberOfJobs * 0.1);
                    for ($i = 0; $i < $numberOfJobs; $i++) {
                        ProcessCampaignMessagesJob::dispatch()->delay($scheduleAt);
                    }
                }
            }
            return $this->engineSuccessResponse([
                'campaignUid' => $campaign->_uid
            ], __tr('Test Message success and Campaign created'));
        }
        return $this->engineFailedResponse([
            'campaignUid' => $campaign->_uid
        ], __tr('Failed to queue messages for campaign'));
    }

    /**
     * Process the queued messages
     *
     * @return EngineResponse
     */
    public function processCampaignSchedule()
    {
        // set that cron job is done
        if (!getAppSettings('cron_setup_using_artisan_at') and app()->runningConsoleCommand('schedule:run')) {
            $this->configurationEngine->processConfigurationsStore('internals', [
                'cron_setup_using_artisan_at' => now()
            ]);
        }
        if (!getAppSettings('queue_setup_using_artisan_at') and app()->runningConsoleCommand('queue:work')) {
            $this->configurationEngine->processConfigurationsStore('internals', [
                'queue_setup_using_artisan_at' => now()
            ]);
        }
        $queuedMessages = $this->whatsAppMessageQueueRepository->getQueueItemsForProcess();
        if (__isEmpty($queuedMessages)) {
            return $this->engineSuccessResponse([], __tr('Nothing to process'));
        }
        $poolData = [];
        foreach ($queuedMessages as $queuedMessage) {
            // try {
            // fetch the latest record
            $queuedMessage = $this->whatsAppMessageQueueRepository->fetchIt($queuedMessage->_id);
            // if record not found or if its already in process
            if (__isEmpty($queuedMessage) || ($queuedMessage->status == 3)) {
                continue;
            }
            $contactsData = $queuedMessage->__data['contact_data'];
            $campaignData = $queuedMessage->__data['campaign_data'];
            $currentPhoneNumberId = ($campaignData['fromPhoneNumberId'] ?? null) ?: getVendorSettings('current_phone_number_id', null, null, $queuedMessage->vendors__id);
            $poolData[$queuedMessage->_uid] = [
                'queueUid' => $queuedMessage->_uid,
                'retries' => $queuedMessage->retries ?: 1,
                'campaignId' => $queuedMessage->campaigns__id,
                'campaignData' => $campaignData,
                'contactsData' => $contactsData,
                'whatsAppTemplateName' => $campaignData['whatsAppTemplateName'],
                'whatsAppTemplateLanguage' => $campaignData['whatsAppTemplateLanguage'],
                'phoneNumber' => $queuedMessage->phone_with_country_code,
                'messageComponents' => $campaignData['messageComponents'],
                'vendorId' => $queuedMessage->vendors__id,
                'currentPhoneNumberId' => $currentPhoneNumberId,
            ];
        }
        $responses = Http::pool(function (Pool $pool) use ($poolData) {
            // Map each request to a pool get request
            $index = 1;
            return array_map(function ($poolRequestItem) use (&$pool, &$index) {
                if (!$poolRequestItem['queueUid']) {
                    return;
                }
                $this->whatsAppMessageQueueRepository->updateIt($poolRequestItem['queueUid'], [
                    'status' => 3, // processing
                ]);
                // set the from number
                fromPhoneNumberIdForRequest($poolRequestItem['currentPhoneNumberId']);
                // prepare the request for pool
                return $this->whatsAppApiService->sendTemplateMessageViaPool($pool, $poolRequestItem['queueUid'], $poolRequestItem['whatsAppTemplateName'], $poolRequestItem['whatsAppTemplateLanguage'], $poolRequestItem['phoneNumber'], $poolRequestItem['messageComponents'], $poolRequestItem['vendorId']);
            }, $poolData);
        });
        $errorMessage = '';
        foreach ($responses as $responseKey => $response) {
            $errorMessage = '';
            $poolRequestItem = $poolData[$responseKey] ?? null;
            // skip it if empty
            if (!$poolRequestItem) {
                continue;
            }
            try {

            // Handle different response types
            if (!$response || !method_exists($response, 'ok')) {
                // Handle error cases
                $errorMessage = '';
                if ($response instanceof \Exception) {
                    $errorMessage = $response->getMessage();
                } elseif (is_object($response) && method_exists($response, 'getMessage')) {
                    $errorMessage = $response->getMessage();
                } else {
                    $errorMessage = 'Unknown error occurred';
                }

                if ($poolRequestItem['retries'] > 5) {
                    // Max retries reached - mark as permanent error
                    $this->whatsAppMessageQueueRepository->updateIt($responseKey, [
                        'status' => 2, // error - do not requeue
                        '__data' => [
                            'process_response' => [
                                'error_message' => $errorMessage,
                                'error_status' => 'error_occurred',
                            ]
                        ]
                    ]);
                } elseif (($response instanceof ConnectException) || ($response instanceof ConnectionException)) {
                    // Connection issues - requeue for retry
                    $this->whatsAppMessageQueueRepository->updateIt($poolRequestItem['queueUid'], [
                        'status' => 1, // re queue
                        'retries' => $poolRequestItem['retries'] + 1,
                        'scheduled_at' => now()->addMinute(), // try in next one minute
                        '__data' => [
                            'process_response' => [
                                'error_status' => 'requeued_connection_error',
                                'error_message' => $errorMessage
                            ]
                        ]
                    ]);
                }
                // Skip to next item
                continue;
            }

            // Safely handle response data
            try {
                $responseData = $response->json();
                if (!isset($responseData['text'])) {
                    $responseData['text'] = '';
                }
                $response = $responseData;
            } catch (\Exception $e) {
                // If json parsing fails, ensure we have a valid response structure
                $response = [
                    'text' => '',
                    'error' => 'Failed to parse response data'
                ];
            }

                if ($response and !$response->ok()) {
                    $response->throw(function (Response $response, $exception) use (&$poolRequestItem, &$errorMessage) {
                        $getContents = $response->getBody()->getContents();
                        $getContentsDecoded = json_decode($getContents, true);
                        $errorMessage = Arr::get($getContentsDecoded, 'error.message', '');
                        return $exception;
                    });
                }
            } catch (\Throwable $th) {
                $consolidatedErrorMessage =  $errorMessage ?: $th->getMessage();
                if ($poolRequestItem['retries'] > 5) {
                    $this->whatsAppMessageQueueRepository->updateIt($responseKey, [
                        'status' => 2, // error - don not requeue
                        '__data' => [
                            'process_response' => [
                                'error_message' => $consolidatedErrorMessage,
                                'error_status' => 'error_occurred',
                            ]
                        ]
                    ]);
                } elseif (($th instanceof ConnectionException) or ($th instanceof ConnectException)) { // if its connection issue we need to retry
                    // requeue the message if connection error
                    $this->whatsAppMessageQueueRepository->updateIt($poolRequestItem['queueUid'], [
                        'status' => 1, // re queue
                        'retries' => $poolRequestItem['retries'] + 1,
                        'scheduled_at' => now()->addMinute(), // try in next one minute
                        '__data' => [
                            'process_response' => [
                                'error_status' => 'requeued_connection_error',
                                'error_message' => $consolidatedErrorMessage
                            ]
                        ]
                    ]);
                } else {
                    /**
                     * @link https://developers.facebook.com/docs/whatsapp/cloud-api/support/error-codes/
                     */
                    // If Cloud API message throughput has been reached we will try to process it again in 1 minute
                    if (Str::contains($consolidatedErrorMessage, '130429')) {
                        $this->whatsAppMessageQueueRepository->updateIt($poolRequestItem['queueUid'], [
                            'status' => 1, // re queue
                            'retries' => $poolRequestItem['retries'] + 1,
                            'scheduled_at' => now()->addMinute(), // try in next one minute
                            '__data' => [
                                'process_response' => [
                                    'error_message' => $consolidatedErrorMessage,
                                    'error_status' => 'requeued_rate_limit_hit',
                                ]
                            ]
                        ]);
                    } else {
                        $this->whatsAppMessageQueueRepository->updateIt($responseKey, [
                            'status' => 2, // error - don not requeue
                            '__data' => [
                                'process_response' => [
                                    'error_message' => $consolidatedErrorMessage,
                                    'error_status' => 'error_occurred',
                                ]
                            ]
                        ]);
                    }
                }
                // as the error occurred no further process is required
                continue;
            }
            if (!$poolRequestItem['queueUid'] or __isEmpty($this->whatsAppMessageQueueRepository->fetchIt($poolRequestItem['queueUid']))) {
                continue;
            }
            $campaignData = $poolRequestItem['campaignData'];
            $contactsData = $poolRequestItem['contactsData'];
            // as the message already sent via pool we have sent result of it to following method
            // as the result of sending already give it won't try to send message again
            $processedResponse = $this->sendActualWhatsAppTemplateMessage(
                $poolRequestItem['vendorId'],
                $contactsData['_id'],
                $poolRequestItem['phoneNumber'],
                $contactsData['_uid'],
                $campaignData['whatsAppTemplateName'],
                $campaignData['whatsAppTemplateLanguage'],
                $campaignData['templateProforma'],
                $campaignData['templateComponents'],
                $campaignData['messageComponents'],
                $poolRequestItem['campaignId'],
                $contactsData,
                ($campaignData['fromPhoneNumberId'] ?? null),
                $response->json() // sent message result
            );
            if ($processedResponse->success()) {
                $this->whatsAppMessageQueueRepository->deleteIt($poolRequestItem['queueUid']);
                $campaignUid = viaFlashCache('campaign_details_' . $poolRequestItem['campaignId'], function () use (&$poolRequestItem) {
                    $campaign = $this->campaignRepository->fetchIt($poolRequestItem['campaignId']);
                    if (!__isEmpty($campaign)) {
                        return $campaign->_uid;
                    }
                    return null;
                });
                $vendorUid = getPublicVendorUid($poolRequestItem['vendorId']);
                // Dispatch event for message
                event(new VendorChannelBroadcast($vendorUid, [
                    'contactUid' => $contactsData['_uid'],
                    'isNewIncomingMessage' => null,
                    'campaignUid' => $campaignUid,
                    'lastMessageUid' => null,
                    'formatted_last_message_time' => null,
                ]));
            }
        }
        return $this->engineSuccessResponse([], __tr('Message processed'));
    }

    /**
     * Template Message Sending Process
     *
     * @param Request $request
     * @param object $contact
     * @param boolean $isForCampaign
     * @param int $campaignId
     * @param int $vendorId
     * @param object $whatsAppTemplate
     * @param array $inputs
     * @return EngineResponse
     */
    public function sendTemplateMessageProcess($request, $contact, $isForCampaign = false, $campaignId = null, $vendorId = null, $whatsAppTemplate = null, $inputs = null)
    {
        $vendorId = $vendorId ?: getVendorId();
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $vendorId);
        if (!$vendorPlanDetails->hasActivePlan()) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }

        $inputs = $inputs ?: $request->all();
        if ($request->template_name and isExternalApiRequest()) {
            $whatsAppTemplate = $whatsAppTemplate ?: $this->whatsAppTemplateRepository->fetchIt([
                'template_name' => $request->template_name,
                'language' => $request->template_language
            ]);
        } else {
            $whatsAppTemplate = $whatsAppTemplate ?: $this->whatsAppTemplateRepository->fetchIt($inputs['template_uid']);
        }

        abortIf(__isEmpty($whatsAppTemplate), null, __tr('Template for the selected language not found in the system, if you have created template recently on Facebook please sync templates again.'));

        $contactWhatsappNumber = $contact->whatsappNumber;
        $templateProforma = Arr::get($whatsAppTemplate->toArray(), '__data.template');
        $templateComponents = Arr::get($templateProforma, 'components');
        $componentValidations = [];
        $bodyComponentText = '';
        $headerComponentText = '';
        $componentButtonText = '';
        $pattern = '/{{\d+}}/';
        foreach ($templateComponents as $templateComponent) {
            if ($templateComponent['type'] == 'HEADER') {
                $headerFormat = $templateComponent['format'];
                if ($headerFormat == 'TEXT') {
                    $headerComponentText = $templateComponent['text'];
                    // Find matches
                    preg_match_all($pattern, $headerComponentText, $headerMatches);
                    array_map(function ($item) use (&$componentValidations) {
                        $item = 'header_field_' . strtr($item, [
                            '{{' => '',
                            '}}' => '',
                        ]);
                        $componentValidations[$item] = [
                            'required',
                        ];

                        return $item;
                    }, $headerMatches[0]); // will contain all matched patterns
                } elseif ($headerFormat == 'LOCATION') {
                    $componentValidations['location_latitude'] = [
                        'required',
                        'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/',
                    ];
                    $componentValidations['location_longitude'] = [
                        'required',
                        'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/',
                    ];
                    $componentValidations['location_name'] = [
                        'required',
                        'string',
                    ];
                    $componentValidations['location_address'] = [
                        'required',
                        'string',
                    ];
                } elseif ($headerFormat == 'IMAGE') {
                    $componentValidations['header_image'] = [
                        'required',
                    ];
                } elseif ($headerFormat == 'VIDEO') {
                    $componentValidations['header_video'] = [
                        'required',
                    ];
                } elseif ($headerFormat == 'DOCUMENT') {
                    $componentValidations['header_document'] = [
                        'required',
                    ];
                    $componentValidations['header_document_name'] = [
                        'required',
                    ];
                }
            } elseif ($templateComponent['type'] == 'BODY') {
                $bodyComponentText = $templateComponent['text'];
                // Find matches
                preg_match_all($pattern, $bodyComponentText, $matches);
                array_map(function ($item) use (&$componentValidations) {
                    $item = 'field_' . strtr($item, [
                        '{{' => '',
                        '}}' => '',
                    ]);
                    $componentValidations[$item] = [
                        'required',
                    ];

                    return $item;
                }, $matches[0]); // will contain all matched patterns
            } elseif ($templateComponent['type'] == 'BUTTONS') {
                $btnIndex = 0;
                foreach ($templateComponent['buttons'] as $templateComponentButton) {
                    if ($templateComponentButton['type'] == 'URL' and (Str::contains($templateComponentButton['url'], '{{1}}'))) {
                        $componentValidations["button_$btnIndex"] = [
                            'required',
                        ];
                    } elseif ($templateComponentButton['type'] == 'COPY_CODE') {
                        $componentValidations['copy_code'] = [
                            'required',
                            'alpha_dash',
                        ];
                    }
                    $btnIndex++;
                }
            }
        }
        if (!$isForCampaign) {
            $request->validate($componentValidations);
        }
        unset($componentValidations);

        // process the data
        // Regular expression to match {{number}}
        $pattern = '/{{\d+}}/';
        // Find matches
        preg_match_all($pattern, $headerComponentText, $headerVariableMatches);
        // $templateParameters = $matches[0]; // will contain all matched patterns
        $headerParameters = array_map(function ($item) {
            return 'header_field_' . strtr($item, [
                '{{' => '',
                '}}' => '',
            ]);
        }, $headerVariableMatches[0]); // will contain all matched patterns

        preg_match_all($pattern, $componentButtonText, $buttonWordsMatches);
        $buttonParameters = array_map(function ($item) {
            return 'button_' . strtr($item, [
                '{{' => '',
                '}}' => '',
            ]);
        }, $buttonWordsMatches[0]); // will contain all matched patterns

        $componentBodyIndex = 0;
        $mainIndex = 0;
        $componentBody = [];
        $componentBody[$mainIndex] = [
            'type' => 'body',
            'parameters' => [],
        ];
        foreach ($inputs as $inputItemKey => $inputItemValue) {
            if (Str::startsWith($inputItemKey, 'field_')) {
                $valueKeyName = str_replace('field_', '', $inputItemKey);
                $componentBody[$mainIndex]['parameters']["{{{$valueKeyName}}}"] = [
                    'type' => 'text',
                    'text' => $this->setParameterValue($contact, $inputs, $inputItemKey),
                ];
            }
            $componentBodyIndex++;
        }
        $componentButtons = [];
        $parametersComponentsCreations = [
            'COPY_CODE',
        ];
        foreach ($templateComponents as $templateComponent) {
            // @link https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages/#media-messages
            // @link https://developers.facebook.com/docs/whatsapp/cloud-api/reference/media#supported-media-types
            if ($templateComponent['type'] == 'HEADER') {
                if ($templateComponent['format'] == 'VIDEO') {
                    $mainIndex++;
                    if (isset($inputs['header_video']) and isValidUrl($inputs['header_video'])) {
                        $inputs['whatsapp_video'] = $inputs['header_video'];
                    } elseif (!isset($inputs['whatsapp_video'])) {
                        $inputHeaderVideo = $inputs['header_video'];
                        $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $inputHeaderVideo], 'whatsapp_video');
                        if ($isProcessed->failed()) {
                            return $isProcessed;
                        }
                        $inputs['whatsapp_video'] = $isProcessed->data('path');
                    }
                    $componentBody[$mainIndex] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'video',
                                'video' => [
                                    'link' => $inputs['whatsapp_video'],
                                ],
                            ],
                        ],
                    ];
                } elseif ($templateComponent['format'] == 'IMAGE') {
                    $mainIndex++;
                    if (isset($inputs['header_image']) and isValidUrl($inputs['header_image'])) {
                        $inputs['whatsapp_image'] = $inputs['header_image'];
                    } elseif (!isset($inputs['whatsapp_image'])) {
                        $inputHeaderImage = $inputs['header_image'];
                        $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $inputHeaderImage], 'whatsapp_image');
                        if ($isProcessed->failed()) {
                            return $isProcessed;
                        }
                        $inputs['whatsapp_image'] = $isProcessed->data('path');
                    }
                    $componentBody[$mainIndex] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'image',
                                'image' => [
                                    'link' => $inputs['whatsapp_image'],
                                ],
                            ],
                        ],
                    ];
                } elseif ($templateComponent['format'] == 'DOCUMENT') {
                    $mainIndex++;
                    $inputHeaderDocument = $inputs['header_document'];
                    if (isset($inputs['header_document']) and isValidUrl($inputs['header_document'])) {
                        $inputs['whatsapp_document'] = $inputs['header_document'];
                    } elseif (!isset($inputs['whatsapp_document'])) {
                        $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $inputHeaderDocument], 'whatsapp_document');
                        if ($isProcessed->failed()) {
                            return $isProcessed;
                        }
                        $inputs['whatsapp_document'] = $isProcessed->data('path');
                    }

                    $componentBody[$mainIndex] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'filename' => $this->setParameterValue($contact, $inputs, 'header_document_name'),
                                    'link' => $inputs['whatsapp_document'],
                                ],
                            ],
                        ],
                    ];
                } elseif ($templateComponent['format'] == 'LOCATION') {
                    // @link https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates/#location
                    $mainIndex++;
                    $componentBody[$mainIndex] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'location',
                                'location' => [
                                    'latitude' => $this->setParameterValue($contact, $inputs, 'location_latitude'),
                                    'longitude' => $this->setParameterValue($contact, $inputs, 'location_longitude'),
                                    'name' => $this->setParameterValue($contact, $inputs, 'location_name'),
                                    'address' => $this->setParameterValue($contact, $inputs, 'location_address'),
                                ],
                            ],
                        ],
                    ];
                } elseif (($templateComponent['format'] == 'TEXT') and Str::contains($templateComponent['text'], '{{1}}')) {
                    // @link https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates
                    $mainIndex++;
                    $componentBody[$mainIndex] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $this->setParameterValue($contact, $inputs, 'header_field_1'),
                            ],
                        ],
                    ];
                }
            } elseif ($templateComponent['type'] == 'CAROUSEL') {
                // Handle carousel template message components
                $carouselComponent = [
                    'type' => 'CAROUSEL',
                    'cards' => []
                ];

                foreach ($templateComponent['cards'] as $cardIndex => $card) {
                    $cardComponent = [
                        'card_index' => $cardIndex,
                        'components' => []
                    ];

                    // Add header component for each card
                    foreach ($card['components'] as $cardComponentData) {
                        if ($cardComponentData['type'] == 'HEADER') {
                            if ($cardComponentData['format'] == 'PRODUCT') {
                                // For product headers, we need to specify product parameters
                                // This would typically come from user input or product catalog
                                $cardComponent['components'][] = [
                                    'type' => 'HEADER',
                                    'parameters' => [
                                        [
                                            'type' => 'PRODUCT',
                                            'product' => [
                                                'product_retailer_id' => 'default_product_' . $cardIndex // Default product ID
                                            ]
                                        ]
                                    ]
                                ];
                            } elseif (in_array($cardComponentData['format'], ['IMAGE', 'VIDEO'])) {
                                // For image/video headers - process uploaded media
                                $mediaType = strtolower($cardComponentData['format']);
                                $inputKey = "carousel_card_{$cardIndex}_{$mediaType}";

                                if (!empty($inputs[$inputKey])) {
                                    // Process uploaded media file
                                    $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $inputs[$inputKey]], "whatsapp_{$mediaType}");
                                    if ($isProcessed->failed()) {
                                        return $isProcessed;
                                    }
                                    $mediaUrl = $isProcessed->data('path');
                                } else {
                                    // Skip this card if no media uploaded
                                    continue 2; // Skip to next card
                                }

                                $cardComponent['components'][] = [
                                    'type' => 'HEADER',
                                    'parameters' => [
                                        [
                                            'type' => strtoupper($cardComponentData['format']),
                                            $mediaType => [
                                                'link' => $mediaUrl
                                            ]
                                        ]
                                    ]
                                ];
                            }
                        } elseif ($cardComponentData['type'] == 'BUTTONS') {
                            // Handle card buttons - only add if they have parameters
                            foreach ($cardComponentData['buttons'] as $buttonIndex => $button) {
                                if ($button['type'] == 'URL' && isset($button['url']) && Str::contains($button['url'], '{{1}}')) {
                                    $cardComponent['components'][] = [
                                        'type' => 'BUTTON',
                                        'sub_type' => 'URL',
                                        'index' => $buttonIndex,
                                        'parameters' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'default_param'
                                            ]
                                        ]
                                    ];
                                }
                                // SPM and QUICK_REPLY buttons don't need parameters
                            }
                        }
                    }

                    $carouselComponent['cards'][] = $cardComponent;
                }

                $componentBody[$mainIndex] = $carouselComponent;
                $mainIndex++;
            } elseif ($templateComponent['type'] == 'BUTTONS') {
                $componentButtonIndex = 0;
                $skipComponentsCreations = [
                    // 'URL',
                    'PHONE_NUMBER',
                ];
                foreach ($templateComponent['buttons'] as $templateComponentButton) {
                    // or check if this type is skipped from components creations
                    if (! in_array($templateComponentButton['type'], $skipComponentsCreations)) {
                        // create component block
                        $componentButtons[$mainIndex] = [
                            'type' => 'button',
                            'sub_type' => $templateComponentButton['type'],
                            'index' => $componentButtonIndex,
                            'parameters' => [],
                        ];
                        // create coupon code parameters
                        if (in_array($templateComponentButton['type'], $parametersComponentsCreations)) {
                            $componentButtons[$mainIndex]['parameters'][] = [
                                'type' => 'COUPON_CODE',
                                'coupon_code' => $this->setParameterValue($contact, $inputs, 'copy_code'),
                            ];
                        } elseif // create url parameters
                        (in_array($templateComponentButton['type'], ['URL']) and Str::contains($templateComponentButton['url'], '{{1}}')) {
                            $componentButtons[$mainIndex]['parameters'][] = [
                                'type' => 'text',
                                'text' => $this->setParameterValue($contact, $inputs, "button_$componentButtonIndex"),
                            ];
                        } elseif // flow button parameters
                        (in_array($templateComponentButton['type'], ['FLOW'])) {
                            $componentButtons[$mainIndex]['parameters'][] = [
                                "type" => "action",
                                'action' => [
                                ]
                            ];
                        }
                    }
                    $componentButtonIndex++;
                    $mainIndex++;
                }
            }
        }
        // remove static links buttons
        foreach ($componentButtons as $componentButtonKey => $componentButton) {
            if (empty($componentButton['parameters'])) {
                unset($componentButtons[$componentButtonKey]);
            }
        }
        $messageComponents = array_merge($componentBody, $componentButtons);
        $contactId = $contact->_id;
        $contactUid = $contact->_uid;

        if ($isForCampaign) {
            return $this->engineSuccessResponse([
                'whatsAppTemplateName' => $whatsAppTemplate->template_name,
                'whatsAppTemplateLanguage' => $whatsAppTemplate->language,
                'templateProforma' => $templateProforma,
                'templateComponents' => $templateComponents,
                'messageComponents' => $messageComponents,
                'inputs' => $inputs,
                'fromPhoneNumberId' => $request->from_phone_number_id,
            ], __tr('Message prepared for WhatsApp campaign'));
        }

        $contactsData = [
            '_id' => $contact->_id,
            '_uid' => $contact->_uid,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'countries__id' => $contact->countries__id,
            'is_template_test_contact' => $request->is_template_test_contact
        ];
        $processedResponse = $this->sendActualWhatsAppTemplateMessage(
            $vendorId,
            $contactId,
            $contactWhatsappNumber,
            $contactUid,
            $whatsAppTemplate->template_name,
            $whatsAppTemplate->language,
            $templateProforma,
            $templateComponents,
            $messageComponents,
            $campaignId,
            $contactsData,
            $request->from_phone_number_id
        );
        $processedResponse->updateData('inputs', $inputs);
        return $processedResponse;
    }

    /**
     * Send Interactive Message Process
     *
     * @param Request $request
     * @param boolean $isMediaMessage
     * @param integer $vendorId
     * @param array $options
     * @return EngineResponse
     */
    public function sendInteractiveMessageProcess($request, bool $isMediaMessage = false, $vendorId = null, $options = [])
    {
        $vendorId = $vendorId ?: getVendorId();
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $vendorId);
        if (!$vendorPlanDetails->hasActivePlan()) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }

        if (is_array($request) === true) {
            $messageBody = $request['messageBody'];
            $contactUid = $request['contactUid'];
        } else {
            $messageBody = $request->message_body;
            $contactUid = $request->contact_uid;
        }
        $contact = $this->contactRepository->getVendorContact($contactUid, $vendorId);
        abortIf(__isEmpty($contact));
        // mark unread chats as read if any
        $this->markAsReadProcess($contact, $vendorId);
        $mediaData = [];
        $serviceName = getAppSettings('name');

        // Get interactive message data from request or options
        $interactiveData = [];
        if (is_array($request) && isset($request['interactive_data'])) {
            $interactiveData = $request['interactive_data'];
        } elseif (!is_array($request) && $request->has('interactive_data')) {
            $interactiveData = $request->interactive_data;
        }

        // Determine interactive type based on data structure
        $interactiveType = null;
        
        // PRIORITY 1: Check for list data in sections
        if (isset($interactiveData['list_data']['sections']) && !empty($interactiveData['list_data']['sections'])) {
            $interactiveType = 'list';
        }
        // PRIORITY 2: Check for sections directly
        elseif (isset($interactiveData['sections']) && !empty($interactiveData['sections'])) {
            $interactiveType = 'list';
        }
        // PRIORITY 3: Check explicit type
        elseif (isset($interactiveData['type']) && $interactiveData['type'] === 'list') {
            $interactiveType = 'list';
        }
        // FALLBACK: Default to button type
        else {
            $interactiveType = 'button';
        }

        // Log the type determination
        \Illuminate\Support\Facades\Log::info('Determined interactive message type', [
            'type' => $interactiveType,
            'has_list_data' => isset($interactiveData['list_data']),
            'has_sections' => isset($interactiveData['sections']),
            'explicit_type' => $interactiveData['type'] ?? 'not_set'
        ]);

        // Prepare interactive message payload according to WhatsApp API structure
        $messagePayload = [
            'type' => 'interactive',
            'interactive' => [
                'type' => $interactiveType
            ]
        ];

        // Add header if provided
        if (!empty($interactiveData['header_text'])) {
            $messagePayload['interactive']['header'] = [
                'type' => 'text',
                'text' => $interactiveData['header_text']
            ];
        }

        // Add body if provided
        if (!empty($interactiveData['body_text'])) {
            $messagePayload['interactive']['body'] = [
                'text' => $interactiveData['body_text']
            ];
        }

        // Add footer if provided
        if (!empty($interactiveData['footer_text'])) {
            $messagePayload['interactive']['footer'] = [
                'text' => $interactiveData['footer_text']
            ];
        }

        // Initialize sections variable for scope
        $sections = [];

        // Handle different interactive types
        if ($interactiveType === 'list') {
            // Process sections for list type
            $sourceSections = [];
            
            // Check both possible locations for sections data
            if (!empty($interactiveData['list_data']['sections'])) {
                $sourceSections = $interactiveData['list_data']['sections'];
            } elseif (!empty($interactiveData['sections'])) {
                $sourceSections = $interactiveData['sections'];
            }

            // Process sections and rows
            foreach ($sourceSections as $section) {
                $processedSection = [
                    'title' => $section['title'] ?? ''
                ];

                $rows = [];
                foreach ($section['rows'] ?? [] as $row) {
                    // Ensure row has required fields
                    if (!empty($row['title'])) {
                        $rows[] = [
                            'id' => (string)($row['id'] ?? $row['row_id'] ?? uniqid()),
                            'title' => $row['title'],
                            'description' => $row['description'] ?? null
                        ];
                    }
                }

                if (!empty($rows)) {
                    $processedSection['rows'] = $rows;
                    $sections[] = $processedSection;
                }
            }

            if (!empty($sections)) {
                // For list type, add action with button and sections
                $messagePayload['interactive']['action'] = [
                    'button' => $interactiveData['list_data']['button_text'] ?? 
                               $interactiveData['button_text'] ?? 
                               'Select an option',
                    'sections' => $sections
                ];

                // Log the final payload for debugging
                \Illuminate\Support\Facades\Log::info('Sending list message payload', [
                    'sections_count' => count($sections),
                    'total_rows' => array_sum(array_map(function($section) { 
                        return count($section['rows'] ?? []); 
                    }, $sections)),
                    'button_text' => $messagePayload['interactive']['action']['button']
                ]);

                // Log list processing for debugging
                \Illuminate\Support\Facades\Log::info('Sending WhatsApp list message', [
                    'sections_count' => count($sections),
                    'total_rows' => array_sum(array_map(function($section) { 
                        return count($section['rows']); 
                    }, $sections)),
                    'button_text' => $messagePayload['interactive']['action']['button']
                ]);

                // Log list processing for debugging
                \Illuminate\Support\Facades\Log::info('Processing list-type interactive message', [
                    'sections_count' => count($sections),
                    'total_rows' => array_sum(array_map(function($section) { 
                        return count($section['rows']); 
                    }, $sections)),
                    'button_text' => $messagePayload['list_data']['button_text']
                ]);
            } else {
                // Fallback to button type if no valid sections
                $messagePayload['interactive_type'] = 'button';
                \Illuminate\Support\Facades\Log::warning('Fallback to button type - no valid sections found');
            }
        

        } elseif ($interactiveType === 'button' && isset($interactiveData['buttons']) && is_array($interactiveData['buttons'])) {
            $messagePayload['interactive']['action'] = [
                'buttons' => array_map(function($button) {
                    $buttonText = is_array($button) ? ($button['title'] ?? $button['text'] ?? '') : $button;
                    return [
                        'type' => 'reply',
                        'reply' => [
                            'id' => Str::random(10),
                            'title' => $buttonText
                        ]
                    ];
                }, $interactiveData['buttons'])
            ];

            // Log button processing
            \Illuminate\Support\Facades\Log::info('Sending WhatsApp button message', [
                'button_count' => count($interactiveData['buttons'])
            ]);
        }

        // Send the interactive message using the WhatsApp API service
        // For list messages, we need to pass the data in the format expected by WhatsAppApiService
        if ($interactiveType === 'list') {
            // Always use the proper list format for WhatsAppApiService, regardless of sections
            $listMessageData = [
                'interactive_type' => 'list',
                'header_text' => $interactiveData['header_text'] ?? '',
                'body_text' => $interactiveData['body_text'] ?? $messageBody ?? '',
                'footer_text' => $interactiveData['footer_text'] ?? '',
                'list_data' => [
                    'button_text' => $interactiveData['list_data']['button_text'] ??
                                   $interactiveData['button_text'] ??
                                   'Select an option',
                    'sections' => $sections
                ]
            ];

            \Illuminate\Support\Facades\Log::info('Sending list message with proper format', [
                'user' => $contact->wa_id,
                'sections_count' => count($sections),
                'button_text' => $listMessageData['list_data']['button_text'],
                'list_data' => $listMessageData['list_data']
            ]);

            $sendMessageResult = $this->whatsAppApiService->sendInteractiveMessage($contact->wa_id, $listMessageData, $vendorId);
        } elseif ($interactiveType === 'button' && isset($interactiveData['buttons'])) {
            // Handle button messages with proper format for WhatsAppApiService
            $buttonMessageData = [
                'interactive_type' => 'button',
                'header_text' => $interactiveData['header_text'] ?? '',
                'body_text' => $interactiveData['body_text'] ?? $messageBody ?? '',
                'footer_text' => $interactiveData['footer_text'] ?? '',
                'buttons' => array_map(function($button) {
                    return is_array($button) ? ($button['title'] ?? $button['text'] ?? '') : $button;
                }, $interactiveData['buttons'])
            ];

            \Illuminate\Support\Facades\Log::info('Sending button message with proper format', [
                'user' => $contact->wa_id,
                'buttons_count' => count($buttonMessageData['buttons']),
                'buttons' => $buttonMessageData['buttons']
            ]);

            $sendMessageResult = $this->whatsAppApiService->sendInteractiveMessage($contact->wa_id, $buttonMessageData, $vendorId);
        } else {
            // Fallback for other message types
            $sendMessageResult = $this->whatsAppApiService->sendInteractiveMessage($contact->wa_id, $messagePayload, $vendorId);
        }
        $messageWamid = Arr::get($sendMessageResult, 'messages.0.id');
        if (! $messageWamid) {
            return $this->engineFailedResponse([
                'contact' => $contact,
            ], __tr('Failed to send message'));
        }
        $this->whatsAppMessageLogRepository->updateOrCreateWhatsAppMessageFromWebhook(
            getVendorSettings('current_phone_number_id', null, null, $vendorId),
            $contact->_id,
            $vendorId,
            $contact->wa_id,
            $messageWamid,
            'accepted',
            $sendMessageResult,
            $messageBody,
            null,
            $mediaData,
            false,
            $options
        );
        // update the client models by existing it
        updateClientModels([
            'whatsappMessageLogs' => $this->contactChatData($contact->_id)->data('whatsappMessageLogs'),
        ], 'prepend');

        return $this->engineSuccessResponse([
            'contact' => $contact,
        ], __tr('Message processed'));
    }

    /**
     * Actual Template Message Process
     *
     * @param integer $vendorId
     * @param integer $contactId
     * @param integer|string $contactWhatsappNumber
     * @param string $contactUid
     * @param string $whatsAppTemplateName
     * @param string $whatsAppTemplateLanguage
     * @param array $templateProforma
     * @param array $templateComponents
     * @param array $messageComponents
     * @param integer|null $campaignId
     * @param array|null $contactsData
     * @return EngineResponse
     */
    protected function sendActualWhatsAppTemplateMessage(
        int $vendorId,
        int $contactId,
        int|string $contactWhatsappNumber,
        string $contactUid,
        string $whatsAppTemplateName,
        string $whatsAppTemplateLanguage,
        array $templateProforma,
        array $templateComponents,
        array $messageComponents,
        ?int $campaignId = null,
        ?array $contactsData = null,
        $fromPhoneNumberId = null,
        $sendMessageResult = null,
    ) {
        $currentPhoneNumberId = $fromPhoneNumberId ?: getVendorSettings('current_phone_number_id', null, null, $vendorId);
        fromPhoneNumberIdForRequest($currentPhoneNumberId);
        if (!$sendMessageResult) {
            $sendMessageResult = $this->whatsAppApiService->sendTemplateMessage($whatsAppTemplateName, $whatsAppTemplateLanguage, $contactWhatsappNumber, $messageComponents, $vendorId);
        }
        $messageResponseStatus = Arr::get($sendMessageResult, 'messages.0.id') ? 'accepted' : null;
        // store it into db
        $recordCreated = $this->whatsAppMessageLogRepository->storeIt([
            'vendors__id' => $vendorId,
            'status' => $messageResponseStatus,
            'contacts__id' => $contactId,
            'campaigns__id' => $campaignId,
            'wab_phone_number_id' => $currentPhoneNumberId,
            'is_incoming_message' => 0,
            'contact_wa_id' => Arr::get($sendMessageResult, 'contacts.0.wa_id'),
            'wamid' => Arr::get($sendMessageResult, 'messages.0.id'),
            '__data' => [
                'contact_data' => $contactsData,
                'initial_response' => $sendMessageResult,
                'template_proforma' => $templateProforma,
                'template_components' => $templateComponents,
                'template_component_values' => $messageComponents,
            ],
        ]);
        if ($messageResponseStatus == 'accepted') {
            return $this->engineSuccessResponse([
                'messageUid' => $recordCreated->_uid,
                'contactUid' => $contactUid,
                'log_message' => $recordCreated,
                'contact' => $this->contactRepository->fetchIt($contactUid),
            ], __tr('Message processed for WhatsApp contact'));
        }

        return $this->engineFailedResponse([], __tr('Failed to process Message for WhatsApp contact. Status: __messageStatus__', [
            '__messageStatus__' => $messageResponseStatus,
        ]));
    }


    /**
     * Mark unread messages as read
     *
     * @param  eloquent  $contact
     * @return void
     */
    public function markAsReadProcess($contact, $vendorId)
    {
        if (!__isEmpty($contact)) {
            if ($contact->lastUnreadMessage) {
                ignoreFacebookApiError(true);
                try {
                    $this->whatsAppApiService->markAsRead($contact->lastUnreadMessage->wa_id, $contact->lastUnreadMessage->wamid, $vendorId);
                } catch (\Throwable $th) {
                    //throw $th;
                }
                ignoreFacebookApiError(false);
            }
            $this->whatsAppMessageLogRepository->markAsRead($contact, $vendorId);
            // update unread count
            $this->updateUnreadCount();
        }
    }

    /**
     * Prepare chat window data
     *
     * @return EngineResponse
     */
    public function chatData(string|null $contactUid, string|null $assigned)
    {
        $vendorId = getVendorId();
        if (isDemo() and isDemoVendorAccount() and !$contactUid) {
            $contactUid = getVendorSettings('test_recipient_contact');
        }
        $this->hasMoreMessages = 0;
        $currentPaginatedPage = request()->page;

        $contact = $this->contactRepository->getVendorContactWithUnreadDetails($contactUid, $vendorId, $assigned);
        // if(!__isEmpty($contact)) {
        // mark unread chats as read
        $this->markAsReadProcess($contact, $vendorId);
        // }
        $dataToSend = [
            // check if received incoming message from contact in last 24 hours
            // the direct message won't be delivered if not received any message by user in last 24 hours
            'isDirectMessageDeliveryWindowOpened' => null,
            'directMessageDeliveryWindowOpenedTillMessage' => '',
            'contact' => null,
            'contacts' => [],
            'whatsappMessageLogs' => [],
            'assigned' => $assigned,
            'currentlyAssignedUserUid' => '',
            'isAiChatBotEnabled' => null,
            // 'assignedLabelIds' => [],
        ];
        if (!request()->ajax()) {
            // labels
            $allLabels = $this->labelRepository->fetchItAll([
                'vendors__id' => $vendorId
            ]);
            // contact custom fields
            $vendorContactCustomFields = $this->contactCustomFieldRepository->fetchItAll([
                'vendors__id' => $vendorId,
            ]);
            // contact groups
            $vendorContactGroups = $this->contactGroupRepository->fetchItAll([
                'vendors__id' => $vendorId,
            ]);
            $dataToSend = array_merge($dataToSend, [
                // get the vendor users having messaging permission
                'vendorMessagingUsers' => $this->userRepository->getVendorMessagingUsers($vendorId),
                'vendorContactGroups' => $vendorContactGroups,
                'vendorContactCustomFields' => $vendorContactCustomFields,
                'allLabels' => $allLabels,
                // 'assignedLabelIds' => $contact?->labels->pluck('_id')->toArray() ?? [],
            ]);
        }
        if (__isEmpty($contact)) {
            return $this->engineSuccessResponse($dataToSend);
        }
        // $contactsData = $this->contactsData($contact)->data();
        updateClientModels([
            'assignedLabelIds' => $contact->labels->pluck('_id')->toArray() ?? [],
            // 'contacts' =>  $this->singleAsContactsData($contact->_uid)->data('contacts'),
            '@contacts' => 'extend'
        ]);
        return $this->engineSuccessResponse(array_merge($dataToSend, [
            // check if received incoming message from contact in last 24 hours
            // the direct message won't be delivered if not received any message by user in last 24 hours
            'isDirectMessageDeliveryWindowOpened' => (! __isEmpty($contact->lastIncomingMessage) and ($contact->lastIncomingMessage?->messaged_at?->diffInHours() < 24)),
            'directMessageDeliveryWindowOpenedTillMessage' => __tr('Reply window open for __availableTimeForReply__', [
                '__availableTimeForReply__' => $contact->lastIncomingMessage?->messaged_at?->addHours(24)->diffForHumans([
                    'parts' => 2,
                    'join' => true,
                ])
            ]),
            'contact' => $contact,
            // 'contacts' => $contactsData['contacts'],
            'contacts' =>  $this->singleAsContactsData($contact->_uid)->data('contacts'),
            // 'contactsPaginatePage' => $contactsData['contactsPaginatePage'],
            'whatsappMessageLogs' => $this->getContactMessagesForChatBox($contact->_id),
            // get the vendor users having messaging permission
            // 'vendorMessagingUsers' => $this->userRepository->getVendorMessagingUsers($vendorId),
            // 'assigned' => $assigned,
            'currentlyAssignedUserUid' => $contact->assignedUser->_uid ?? '',
            'isAiChatBotEnabled' => !$contact->disable_ai_bot,
            // 'vendorContactGroups' => $vendorContactGroups,
            // 'vendorContactCustomFields' => $vendorContactCustomFields,
            // 'allLabels' => $allLabels,
            // 'assignedLabelIds' => $contact->labels->pluck('_id')->toArray() ?? [],
            'messagePaginatePage' => ($this->hasMoreMessages ? ($currentPaginatedPage ? ($currentPaginatedPage + 1) : 2) : 0)
        ]));
    }

    /**
     * Prepare the contact messages for the chat box
     *
     * @param integer $contactId
     * @param boolean $onlyRecent
     * @return object
     */
    protected $hasMoreMessages = false;
    protected function getContactMessagesForChatBox(int $contactId, bool $onlyRecent = false)
    {
        if (! $onlyRecent) {
            $resultOfMessages = $this->whatsAppMessageLogRepository->allMessagesOfContact($contactId);
        } else {
            $resultOfMessages = $this->whatsAppMessageLogRepository->recentMessagesOfContact($contactId);
        }
        $this->hasMoreMessages = $resultOfMessages->hasMorePages();
        return $resultOfMessages->keyBy('_uid')->transform(function ($item, string $key) {
            $item->message = $this->formatWhatsAppText($item->message);
            $item->template_message = null;
            if (! $item->message || Arr::get($item->__data, 'interaction_message_data')) {
                $item->template_message = $this->compileMessageWithValues($item->__data);
            }
            return $item;
        });
    }

    /**
     * Prepare Single chat logs for current selected user
     *
     * @return EngineResponse
     */
    public function contactChatData(string|int $contactIdOrUid)
    {
        $contactId = is_string($contactIdOrUid) ? $this->contactRepository->getVendorContact($contactIdOrUid)->_id : $contactIdOrUid;

        return $this->engineSuccessResponse([
            'whatsappMessageLogs' => $this->getContactMessagesForChatBox($contactId, true),
        ]);
    }

    /**
     * Prepare Single chat logs for current selected user
     *
     * @param  string|int  $contactUid
     * @param  string|null  $assigned
     * @return EngineResponse
     */
    protected $hasMoreContacts = false;
    public function contactsData($contactUid, $assigned = null)
    {
        $vendorId = getVendorId();
        if (is_string($contactUid)) {
            $contact = $this->contactRepository->getVendorContactWithUnreadDetails($contactUid, $vendorId, $assigned);
        } else {
            $contact = $contactUid;
        }
        $this->hasMoreContacts = 0;
        $currentPaginatedPage = request()->page;

        $vendorContactsWithUnreadDetails = $this->contactRepository->getVendorContactsWithUnreadDetails($vendorId, $assigned);
        $this->hasMoreContacts = $vendorContactsWithUnreadDetails->hasMorePages();
        if (!__isEmpty($contact)) {
            $isContactInTheList = $vendorContactsWithUnreadDetails->where('_id', $contact->_id)->count();
            if (!$isContactInTheList) {
                $vendorContactsWithUnreadDetails = $vendorContactsWithUnreadDetails->toBase()->merge([$contact]);
            }
        }
        $responseEngineData = [
            'contacts' => $vendorContactsWithUnreadDetails->keyBy('_uid'),
        ];
        if (!request()->request_contact) {
            $responseEngineData['contactsPaginatePage'] = ($this->hasMoreContacts ? ($currentPaginatedPage ? ($currentPaginatedPage + 1) : 2) : 0);
        }
        return $this->engineSuccessResponse($responseEngineData);
    }
    /**
     * Prepare Single chat logs for current selected user
     *
     * @param  string|int  $contactUid
     * @param  string|null  $assigned
     * @return EngineResponse
     */
    protected function singleAsContactsData($contactUid, $assigned = null)
    {
        $vendorId = getVendorId();
        if (is_string($contactUid)) {
            $contact = $this->contactRepository->getVendorContactWithUnreadDetails($contactUid, $vendorId, $assigned);
        } else {
            $contact = $contactUid;
        }
        // dummy query
        $vendorContactsWithUnreadDetails = $this->contactRepository->getVendorContactsWithUnreadDetails('NOTHING', $assigned);
        $this->hasMoreContacts = $vendorContactsWithUnreadDetails->hasMorePages();
        if (!__isEmpty($contact)) {
            $isContactInTheList = $vendorContactsWithUnreadDetails->where('_id', $contact->_id)->count();
            if (!$isContactInTheList) {
                $vendorContactsWithUnreadDetails = $vendorContactsWithUnreadDetails->toBase()->merge([$contact]);
            }
        }
        return $this->engineSuccessResponse([
            'contacts' => $vendorContactsWithUnreadDetails->keyBy('_uid')
        ]);
    }

    /**
     * Compile Message with required values
     *
     * @param array $messageData
     * @return view|string
     */
    protected function compileMessageWithValues($messageData)
    {
        if (isset($messageData['interaction_message_data'])) {
            return view('whatsapp-service.interaction-message-partial', [
                'mediaValues' => array_merge([
                    'media_link' => '',
                    'header_type' => '', // "text", "image", or "video"
                    'header_text' => '',
                    'body_text' => '',
                    'footer_text' => '',
                    'buttons' => [
                    ],
                ], $messageData['interaction_message_data']),
            ])->render();
        } elseif (isset($messageData['media_values'])) {
            $messageData['media_values']['caption'] = $this->formatWhatsAppText($messageData['media_values']['caption'] ?? '');
            return view('whatsapp-service.media-message-partial', [
                'mediaValues' => array_merge([
                    'link' => null,
                    'type' => null,
                    'caption' => null,
                    'file_name' => null,
                    'original_filename' => null,
                ], $messageData['media_values']),
            ])->render();
        }

        if (! isset($messageData['template_components']) or ! isset($messageData['template_component_values'])) {
            return null;
        }
        $templateComponents = $messageData['template_components'];
        $templateComponentValues = $messageData['template_component_values'];
        $bodyItemValues = [];
        $headerItemValues = [
            'image' => null,
            'video' => null,
            'document' => null,
            'location' => null,
            'text' => [],
        ];
        $buttonIndex = 1;
        $buttonValues = [];
        foreach ($templateComponentValues as $templateComponentValue) {
            if ($templateComponentValue['type'] == 'body') {
                foreach ($templateComponentValue['parameters'] as $templateComponentValueParameterKey => $templateComponentValueParameter) {
                    $bodyItemValues[$templateComponentValueParameterKey] = $templateComponentValueParameter['text'];
                }
            } elseif ($templateComponentValue['type'] == 'header') {
                foreach ($templateComponentValue['parameters'] as $templateComponentValueParameterKey => $templateComponentValueParameter) {
                    if ($templateComponentValueParameter['type'] == 'image') {
                        $headerItemValues['image'] = $templateComponentValueParameter['image']['link'] ?? '';
                    } elseif ($templateComponentValueParameter['type'] == 'video') {
                        $headerItemValues['video'] = $templateComponentValueParameter['video']['link'] ?? '';
                    } elseif ($templateComponentValueParameter['type'] == 'document') {
                        $headerItemValues['document'] = $templateComponentValueParameter['document']['link'] ?? '';
                    } elseif ($templateComponentValueParameter['type'] == 'location') {
                        $headerItemValues['location'] = $templateComponentValueParameter['location'] ?? [
                            'name' => '',
                            'address' => '',
                            'latitude' => null,
                            'longitude' => null,
                        ];
                    } elseif ($templateComponentValueParameter['type'] == 'text') {
                        $headerItemValues['text'][] = $templateComponentValueParameter['text'];
                    }
                }
            } elseif ($templateComponentValue['type'] == 'button') {
                if ($templateComponentValue['sub_type'] == 'URL') {
                    if (isset($templateComponentValue['parameters'][0]['text'])) {
                        $buttonValues["{{{$buttonIndex}}}"] = $templateComponentValue['parameters'][0]['text'];
                        $buttonIndex++;
                    }
                } elseif ($templateComponentValue['sub_type'] == 'COPY_CODE') {
                    $buttonValues['COPY_CODE'] = $templateComponentValue['parameters'][0]['coupon_code'] ?? null;
                }
            }
        }

        return view('whatsapp-service.message-template-partial', array_merge($this->prepareTemplate('for_message', [
            'templateComponents' => $templateComponents,
        ]), [
            'templateComponentValues' => $templateComponentValues,
            'headerItemValues' => $headerItemValues,
            'bodyItemValues' => $bodyItemValues,
            'buttonValues' => $buttonValues,
        ]))->render();
    }

    /**
     * Clear Chat history for the Contact
     *
     * @param string $contactUid
     * @return void
     */
    public function processClearChatHistory($contactUid)
    {
        $vendorId = getVendorId();
        $contact = $this->contactRepository->getVendorContact($contactUid, $vendorId);
        abortIf(__isEmpty($contact));
        if ($this->whatsAppMessageLogRepository->clearChatHistory($contact->_id, $contact->vendors__id)) {
            updateClientModels([
                'whatsappMessageLogs' => $this->getContactMessagesForChatBox($contact->_id, true),
            ]);

            return $this->engineSuccessResponse([], __tr('Chat history has been cleared for __contactFullName__', [
                '__contactFullName__' => $contact->full_name,
            ]));
        }

        return $this->engineFailedResponse([], __tr('No chat history to clear for __contactFullName__', [
            '__contactFullName__' => $contact->full_name,
        ]));
    }

    /**
     * Send Chat Message Process
     *
     * @param Request $request
     * @param boolean $isMediaMessage
     * @param integer $vendorId
     * @param array $options
     * @return EngineResponse
     */
    public function processSendChatMessage($request, bool $isMediaMessage = false, $vendorId = null, $options = [])
    {
        $vendorId = $vendorId ?: getVendorId();
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $vendorId);
        if (!$vendorPlanDetails->hasActivePlan()) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }
        $interactionMessageData = null;
        $mediaMessageData = null;
        $fromPhoneNumberId = null;
        if (is_array($request) === true) {
            $messageBody = $request['messageBody'];
            $contactUid = $request['contactUid'];
            if (isset($options['interaction_message_data']) and !empty($options['interaction_message_data'])) {
                $interactionMessageData = $options['interaction_message_data'];
            }
            if (isset($options['media_message_data']) and !empty($options['media_message_data'])) {
                $mediaMessageData = $options['media_message_data'];
            }
        } else {
            $messageBody = $request->message_body;
            $contactUid = $request->contact_uid;
            $fromPhoneNumberId = $request->from_phone_number_id;
        }
        // options extend
        $options = array_merge([
            'from_phone_number_id' => $fromPhoneNumberId,
            'messageWamid' => null,
        ], $options);

        $contact = $this->contactRepository->getVendorContact($contactUid, $vendorId);
        if (__isEmpty($contact)) {
            if (isExternalApiRequest()) {
                $contact = $this->createAContactForApiRequest($request);
            } else {
                return $this->engineFailedResponse([], __tr('Requested contact does not found'));
            }
        }
        $currentBusinessPhoneNumber = $options['from_phone_number_id'] ?: ($contact->lastMessage->wab_phone_number_id ?? getVendorSettings('current_phone_number_id', null, null, $vendorId));
        fromPhoneNumberIdForRequest($currentBusinessPhoneNumber);
        $initializeLogMessage = null;
        // to show message in chat instantly we have created first log entry
        // only for chat box
        if (!isExternalApiRequest() and $messageBody) {
            $initializeLogMessage = $this->whatsAppMessageLogRepository->storeIt([
                'is_incoming_message' => 0,
                'status' => 'initialize',
                'contact_wa_id' => $contact->wa_id,
                'contacts__id' => $contact->_id,
                'vendors__id' => $vendorId,
                'wab_phone_number_id' => $currentBusinessPhoneNumber,
                'message' => $messageBody,
                'messaged_at' => now(),
                '__data' => [
                    'options' => Arr::only($options, [
                        'bot_reply',
                        'ai_bot_reply',
                    ]),
                ],
            ]);
            // update message list
            updateClientModelsViaEvent([
                'whatsappMessageLogs' => $this->contactChatData($contact->_id)->data('whatsappMessageLogs'),
            ], 'prepend');
            // useful for scrolling
            dispatchStreamEventData('onChatBoxMessageSubmit', []);
            $options['message_log_id'] = $initializeLogMessage->_id;
        }
        // do not mark messages as unread if bot replies sent or ai triggered
        if ((Arr::get($options, 'ai_error_triggered') != true) and (Arr::get($options, 'bot_reply') != true)) {
            // mark unread chats as read if any
            $this->markAsReadProcess($contact, $vendorId);
        }

        $mediaData = [];
        $serviceName = getAppSettings('name');
        $contact = $this->contactRepository->getVendorContact($contactUid, $vendorId);
        if (__isEmpty($contact)) {
            if (isExternalApiRequest()) {
                $contact = $this->createAContactForApiRequest($request);
            } else {
                return $this->engineFailedResponse([], __tr('Requested contact does not found'));
            }
        }

        // Log interaction message data
        \Illuminate\Support\Facades\Log::info('Interactive Message Data', [
            'interaction_data' => $interactionMessageData
        ]);

        if ($interactionMessageData) {
            $serviceName = getAppSettings('name');

            // Format the message data for WhatsApp API
            $messagePayload = [
                'type' => 'interactive'
            ];

            // Handle list type messages
            if (isset($interactionMessageData['interactive_type']) && $interactionMessageData['interactive_type'] === 'list') {
                $messagePayload['interactive'] = [
                    'type' => 'list',
                    'body' => [
                        'text' => isDemo() ? "{$serviceName} DEMO - " . $interactionMessageData['body_text'] : $interactionMessageData['body_text']
                    ],
                    'action' => [
                        'button' => $interactionMessageData['list_data']['button_text'] ?? 'Select an option',
                        'sections' => []
                    ]
                ];

                // Add header if present
                if (!empty($interactionMessageData['header_text'])) {
                    $messagePayload['interactive']['header'] = [
                        'type' => 'text',
                        'text' => $interactionMessageData['header_text']
                    ];
                }

                // Add footer if present
                if (!empty($interactionMessageData['footer_text'])) {
                    $messagePayload['interactive']['footer'] = [
                        'text' => $interactionMessageData['footer_text']
                    ];
                }

                // Process sections
                if (isset($interactionMessageData['list_data']['sections'])) {
                    foreach ($interactionMessageData['list_data']['sections'] as $section) {
                        $processedSection = [
                            'title' => $section['title']
                        ];

                        $rows = [];
                        foreach ($section['rows'] as $row) {
                            // Handle both 'row_id' and 'id' fields for backward compatibility
                            $rowId = $row['row_id'] ?? $row['id'] ?? '';

                            $rows[] = [
                                'id' => (string)$rowId,
                                'title' => $row['title'] ?? '',
                                'description' => $row['description'] ?? ''
                            ];
                        }

                        if (!empty($rows)) {
                            $processedSection['rows'] = $rows;
                            $messagePayload['interactive']['action']['sections'][] = $processedSection;
                        }
                    }
                }

                // Log the final payload
                \Illuminate\Support\Facades\Log::info('Sending List Message Payload', [
                    'payload' => $messagePayload
                ]);

                // Convert messagePayload to the format expected by WhatsAppApiService
                $apiServiceData = [
                    'interactive_type' => 'list',
                    'header_text' => $messagePayload['interactive']['header']['text'] ?? '',
                    'body_text' => $messagePayload['interactive']['body']['text'] ?? '',
                    'footer_text' => $messagePayload['interactive']['footer']['text'] ?? '',
                    'list_data' => [
                        'button_text' => $messagePayload['interactive']['action']['button'] ?? 'Select an option',
                        'sections' => $messagePayload['interactive']['action']['sections'] ?? []
                    ]
                ];

                \Illuminate\Support\Facades\Log::info('Converted list message for API service', [
                    'api_service_data' => $apiServiceData
                ]);

                $sendMessageResult = $this->whatsAppApiService->sendInteractiveMessage(
                    $contact->wa_id,
                    $apiServiceData,
                    $contact->vendors__id
                );
            } else {
                // Handle other interactive message types (buttons, etc.)
                $interactionMessageData['body_text'] = isDemo() ? "{$serviceName} DEMO - " . $interactionMessageData['body_text'] : $interactionMessageData['body_text'];

                \Illuminate\Support\Facades\Log::info('Sending non-list interactive message', [
                    'interaction_data' => $interactionMessageData
                ]);

                $sendMessageResult = $this->whatsAppApiService->sendInteractiveMessage(
                    $contact->wa_id,
                    $interactionMessageData,
                    $contact->vendors__id
                );
            }
        } elseif ($isMediaMessage) {
            $fileUrl = $fileName = $fileOriginalName = null;
            $rawUploadData = [];
            $caption = $request->caption ?? '';
            $mediaType = $request->media_type ?? '';
            if ($mediaMessageData) {
                $fileName = $mediaMessageData['file_name'];
                $fileUrl = $mediaMessageData['media_link'];
                $fileOriginalName = $mediaMessageData['file_name'];
                $caption = $mediaMessageData['caption'];
                $mediaType = $mediaMessageData['header_type'];
            } elseif (!isValidUrl($request->media_url)) {
                $rawUploadData = json_decode($request->raw_upload_data, true);
                $isProcessed = $this->mediaEngine->whatsappMediaUploadProcess(['filepond' => $request->uploaded_media_file_name], 'whatsapp_' . $mediaType);
                if ($isProcessed->failed()) {
                    return $isProcessed;
                }
                $fileUrl = $isProcessed->data('path');
                $fileName = $isProcessed->data('fileName');
                $fileOriginalName = Arr::get($rawUploadData, 'original_filename');
            } else {
                $fileName = $request->file_name;
                $fileUrl = $request->media_url;
                $fileOriginalName = $fileName;
            }
            $sendMessageResult = $this->whatsAppApiService->sendMediaMessage($contact->wa_id, $mediaType, $fileUrl, (isDemo() ? "{$serviceName} DEMO - " . $caption : '' . $caption), $fileOriginalName, $vendorId);
            $mediaData = [
                'type' => $mediaType,
                'link' => $fileUrl,
                'caption' => $caption,
                'mime_type' => Arr::get($rawUploadData, 'fileMimeType'),
                'file_name' => $fileName,
                'original_filename' => $fileOriginalName,
            ];
        } else {
            $sendMessageResult = $this->whatsAppApiService->sendMessage($contact->wa_id, (isDemo() ? "`{$serviceName} DEMO`\n\r\n\r " . $messageBody : '' . $messageBody), $vendorId, [
                'repliedToMessageWamid' => $options['messageWamid']
            ]);
        }
        $messageWamid = Arr::get($sendMessageResult, 'messages.0.id');
        if (! $messageWamid) {
            if ($initializeLogMessage) {
                $initializeLogMessage->status =  'failed';
                $initializeLogMessage->save();
            }
            return $this->engineFailedResponse([
                'contact' => $contact,
            ], __tr('Failed to send message'));
        }
        if ($initializeLogMessage) {
            $initializeLogMessage->wamid =  $messageWamid;
            $initializeLogMessage->save();
        }
        $logMessage = $this->whatsAppMessageLogRepository->updateOrCreateWhatsAppMessageFromWebhook(
            $currentBusinessPhoneNumber,
            $contact->_id,
            $vendorId,
            $contact->wa_id,
            $messageWamid,
            'accepted',
            $sendMessageResult,
            $messageBody,
            null,
            $mediaData,
            false,
            $options
        );
        // update the client models by existing it
        updateClientModels([
            'whatsappMessageLogs' => $this->contactChatData($contact->_id)->data('whatsappMessageLogs'),
        ], 'prepend');

        return $this->engineSuccessResponse([
            'contact' => $contact,
            'log_message' => $logMessage,
        ], isExternalApiRequest() ? __tr('Message processed') : '');
    }

    /**
     * Set the Parameters to to concerned template dynamic values
     *
     * @param object $contact
     * @param array $inputs
     * @param mixed $item
     * @return mixed
     */
    protected function setParameterValue(&$contact, &$inputs, $item)
    {
        $inputValue = $inputs[$item];

        if (isExternalApiRequest()) {
            return $this->dynamicValuesReplacement($inputValue, $contact);
        }
        // for any internal requests
        if (Str::startsWith($inputValue, 'dynamic_contact_')) {
            // assign phone value
            if ($inputValue == 'dynamic_contact_phone_number') {
                $inputValue = 'dynamic_contact_wa_id';
            }
            // check if value permitted
            if (!array_key_exists($inputValue, configItem('contact_data_mapping'))) {
                return null;
            }
            // correct the name
            $fieldName = str_replace('dynamic_contact_', '', $inputValue);
            // country value
            switch ($fieldName) {
                case 'country':
                    return $contact->country?->name ?: '-';
                    break;
            }
            return $contact->{$fieldName} ?: '-';
            // custom field values
        } elseif (Str::startsWith($inputValue, 'contact_custom_field_')) {
            $fieldName = str_replace('contact_custom_field_', '', $inputValue);
            // for api external request find value by field name
            if (isExternalApiRequest()) {
                return $contact->valueWithField?->firstWhere('customField.input_name', $fieldName)?->field_value ?: '-';
            }
            return $contact->customFieldValues?->firstWhere('contact_custom_fields__id', $fieldName)?->field_value ?: '-';
        }
        return $inputs[$item] ?: '-';
    }

    /**
     * Prepare string with values replacements
     *
     * @param string $inputValue
     * @param Eloquent $contact
     * @return string
     */
    protected function dynamicValuesReplacement($inputValue, &$contact)
    {
        $dynamicFieldsToReplace = [
            '{first_name}' => $contact->first_name,
            '{last_name}' => $contact->last_name,
            '{full_name}' => $contact->first_name . ' ' . $contact->last_name,
            '{phone_number}' => $contact->wa_id,
            '{email}' => $contact->email,
            '{country}' => $contact->country?->name,
            '{language_code}' => $contact->language_code,
        ];
        // Review this code and make the appropriate changes
        $valueWithFields = $contact->valueWithField;
        foreach ($valueWithFields as $valueWithField) {
            if ($valueWithField?->customField?->input_name) {
                $dynamicFieldsToReplace["{{$valueWithField->customField->input_name}}"] = $valueWithFields?->firstWhere('customField.input_name', $valueWithField->customField->input_name)?->field_value;
            }
        }
        // assign dynamic values
        return strtr($inputValue, $dynamicFieldsToReplace);
    }

    /**
     * Send message processed by bot reply
     *
     * @param string $contactUid
     * @param string $replyText
     * @param int $vendorId
     * @param array|null $interactionMessageData
     * @return void
     */
    protected function sendReplyBotMessage($contactUid, $replyText, $vendorId, $interactionMessageData = null, $options = [])
    {
        $options = array_merge([
            'from_phone_number_id' => null,
            'messageWamid' => null,
        ], $options);

        $mediaMessageData = $options['mediaMessageData'] ?? null;
        return $this->processSendChatMessage(
            [
            'contactUid' => $contactUid,
            'messageBody' => $replyText,
        ],
            $mediaMessageData ? true : false,
            $vendorId,
            [
            'bot_reply' => true,
            'ai_bot_reply' => $options['ai_bot_reply'] ?? false,
            'ai_error_triggered' => $options['ai_error_triggered'] ?? false,
            'media_message_data' => $mediaMessageData,
            'interaction_message_data' => $interactionMessageData,
            'from_phone_number_id' => $options['from_phone_number_id'],
            'messageWamid' => $options['messageWamid'],
        ]
        );
    }

    /**
     * Validate Bot Reply
     *
     * @param integer $testBotId
     * @return EngineResponse
     */
    public function validateTestBotReply(int $testBotId)
    {
        $testContactUid = getVendorSettings('test_recipient_contact');
        if (!$testContactUid) {
            return $this->engineFailedResponse([], __tr('Test Contact missing, You need to set the Test Contact first, do it under the WhatsApp Settings'));
        }
        $contact = $this->contactRepository->getVendorContact($testContactUid);
        if (__isEmpty($contact)) {
            return $this->engineFailedResponse([], __tr('Test contact does not found'));
        }
        return $this->processReplyBot($contact, '', $testBotId);
    }

    /**
     * Process the bot reply if required
     *
     * @param Eloquent Object $contact
     * @param string $messageBody
     * @return void
     */
    public function processReplyBot($contact, $messageBody, $testBotId = null, $options = [])
    {
        if (__isEmpty($contact)) {
            return false;
        }
        $options = array_merge([
            'fromPhoneNumberId' => null,
            'messageWamid' => null,
        ], $options);
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $contact->vendors__id);
        if (!$vendorPlanDetails->hasActivePlan()) {
            return false;
        }
        $messageBody = strtolower(trim($messageBody));
        $dataFetchConditions = [
            'vendors__id' => $contact->vendors__id,
        ];
        if ($testBotId) {
            $dataFetchConditions['_id'] = $testBotId;
        } else {
            if (!$messageBody) {
                return $this->engineFailedResponse([], __tr('Message body is empty'));
            }
        }

        $isBotTimingsEnabled = getVendorSettings('enable_bot_timing_restrictions', null, null, $contact->vendors__id);
        $isAiBotTimingsEnabled = getVendorSettings('enable_ai_bot_timing_restrictions', null, null, $contact->vendors__id);
        $isBotTimingsInTime = $this->isInAllowedBotTiming($contact->vendors__id);
        $selectedOtherBotsForTimingRestrictions = getVendorSettings('enable_selected_other_bot_timing_restrictions', null, null, $contact->vendors__id) ?: [];
        // Get active flow for this user if any
        $activeFlow = UserActiveFlow::getActiveFlow($contact->wa_id);
        
        // Log the current state for debugging
        Log::info('Processing WhatsApp message', [
            'user' => $contact->wa_id,
            'message' => $messageBody,
            'active_flow' => $activeFlow ? $activeFlow->flow_id : 'none'
        ]);

        // PRIORITY CHECK: If there's an active flow waiting for input, try to process it first
        if ($activeFlow && isset($activeFlow->__data['waiting_for_input']) && $activeFlow->__data['waiting_for_input']) {
            Log::info('Active flow waiting for input detected, processing with FlowIntegrationService', [
                'user' => $contact->wa_id,
                'flow_id' => $activeFlow->flow_id,
                'current_node' => $activeFlow->__data['current_node_id'] ?? 'unknown',
                'message' => $messageBody
            ]);

            // Get any bot reply from the active flow to use as context
            $flowBotReply = $this->botReplyRepository->fetchIt([
                'bot_flows__id' => $activeFlow->flow_id,
                'vendors__id' => $contact->vendors__id
            ]);

            if ($flowBotReply) {
                // Try to process with the flow integration service
                $flowIntegrationService = new FlowIntegrationService();
                $nodeFlowResult = $flowIntegrationService->processNodeBasedFlow($contact, $messageBody, $flowBotReply, $options);

                if ($nodeFlowResult && $nodeFlowResult['processed_by_new_flow']) {
                    Log::info('User input processed by FlowIntegrationService', [
                        'user' => $contact->wa_id,
                        'flow_id' => $activeFlow->flow_id,
                        'response_count' => count($nodeFlowResult['responses'] ?? []),
                        'is_complete' => $nodeFlowResult['is_complete'] ?? false
                    ]);

                    // Process responses from flow system
                    foreach ($nodeFlowResult['responses'] as $response) {
                        $responseText = $response['text'] ?? '';
                        $interactionData = null;
                        $mediaMessageData = null;

                        // Handle different response types
                        if ($response['type'] === 'interactive') {
                            if (isset($response['list_data'])) {
                                $sections = [];
                                foreach ($response['list_data']['sections'] as $section) {
                                    $rows = [];
                                    foreach ($section['rows'] as $row) {
                                        $rows[] = [
                                            'id' => (string)($row['id'] ?? $row['row_id'] ?? uniqid()),
                                            'title' => $row['title'],
                                            'description' => $row['description'] ?? null
                                        ];
                                    }
                                    if (!empty($rows)) {
                                        $sections[] = [
                                            'title' => $section['title'],
                                            'rows' => $rows
                                        ];
                                    }
                                }

                                $interactionData = [
                                    'body_text' => $responseText,
                                    'interactive_type' => 'list',
                                    'list_data' => [
                                        'button_text' => $response['list_data']['button_text'] ?? 'Select an option',
                                        'sections' => $sections
                                    ]
                                ];
                                $responseText = '';
                            } elseif (isset($response['buttons'])) {
                                $buttons = [];
                                foreach ($response['buttons'] as $button) {
                                    $buttons[] = $button['title'];
                                }
                                $interactionData = [
                                    'body_text' => $responseText,
                                    'interactive_type' => 'button',
                                    'buttons' => $buttons,
                                    'header_text' => $response['header_text'] ?? '',
                                    'footer_text' => $response['footer_text'] ?? ''
                                ];
                                $responseText = '';
                            }
                        } elseif ($response['type'] === 'media_message') {
                            $mediaMessageData = [
                                'header_type' => $response['media_type'] ?? 'document',
                                'media_link' => $response['media_url'] ?? '',
                                'caption' => $response['caption'] ?? '',
                                'file_name' => $response['filename'] ?? ''
                            ];
                            $responseText = '';
                        }

                        // Send the response
                        $messageOptions = [
                            'mediaMessageData' => $mediaMessageData,
                            'from_phone_number_id' => $options['fromPhoneNumberId'],
                            'node_based_flow' => true
                        ];

                        if ($response['type'] !== 'message') {
                            $messageOptions['messageWamid'] = $options['messageWamid'];
                        }

                        $this->sendReplyBotMessage($contact->_uid, $responseText, $contact->vendors__id, $interactionData, $messageOptions);
                    }

                    if ($testBotId) {
                        return $this->engineSuccessResponse([], __tr('Flow input processed successfully'));
                    }

                    // Successfully processed by flow system, return early
                    return;
                }
            }
        }
        
        // Get bot replies based on whether there's an active flow
        $allBotReplies = $this->botReplyRepository->getRelatedOrWelcomeBots($dataFetchConditions)->sortBy('priority_index');

        // Also get bot flows that can be triggered directly (welcome and new_message types)
        $triggerableFlows = $this->botFlowRepository->getTriggerableFlows([
            'vendors__id' => $contact->vendors__id
        ]);

        // Convert bot flows to bot reply-like objects for processing
        $flowReplies = $triggerableFlows->map(function($flow) {
            // Create a pseudo bot reply object for flow processing
            return new PseudoBotReply([
                '_id' => null, // No bot reply ID
                '_uid' => 'flow_' . $flow->_uid, // Unique identifier
                'reply_trigger' => $flow->start_trigger,
                'reply_text' => '', // Will be handled by flow system
                'trigger_type' => $flow->trigger_type,
                'priority_index' => 0, // High priority for direct flows
                '__data' => [],
                'bot_flows__id' => $flow->_id,
                'status' => $flow->status,
                'botFlow' => $flow, // Attach the flow object
            ]);
        });

        // Merge bot replies with flow replies
        $allBotReplies = $allBotReplies->merge($flowReplies)->sortBy('priority_index');

        Log::info('Retrieved bot replies for processing', [
            'user' => $contact->wa_id,
            'message' => $messageBody,
            'total_replies' => $allBotReplies->count(),
            'flow_replies' => $allBotReplies->where('bot_flows__id', '!=', null)->count(),
            'non_flow_replies' => $allBotReplies->where('bot_flows__id', null)->count(),
            'new_message_replies' => $allBotReplies->where('trigger_type', 'new_message')->count(),
            'welcome_replies' => $allBotReplies->where('trigger_type', 'welcome')->count(),
            'direct_flow_replies' => $flowReplies->count(),
            'trigger_types' => $allBotReplies->pluck('trigger_type')->unique()->values()->toArray(),
            'active_flow' => $activeFlow ? $activeFlow->flow_id : 'none'
        ]);
        
        $isBotMatched = false;
        if (!__isEmpty($allBotReplies)) {
            // FIRST: Check if the message matches any flow's start_trigger
            // This should happen BEFORE filtering, regardless of whether there's an active flow
            foreach ($allBotReplies as $botReply) {
                // Check if this is a flow start trigger that matches the message
                if ($botReply->bot_flows__id && $botReply->botFlow && $botReply->botFlow->start_trigger) {
                    $triggerType = $botReply->botFlow->trigger_type ?? 'is';
                    $isFlowStartMatch = $this->checkTriggerMatch($messageBody, $botReply->botFlow->start_trigger, $triggerType);

                    if ($isFlowStartMatch) {
                        if ($activeFlow && $activeFlow->flow_id !== $botReply->bot_flows__id) {
                            // This message matches a start trigger from a different flow
                            // Clear the current active flow and let the new flow be processed
                            Log::info('Message matches start trigger of different flow, clearing active flow', [
                                'user' => $contact->wa_id,
                                'current_active_flow_id' => $activeFlow->flow_id,
                                'new_flow_id' => $botReply->bot_flows__id,
                                'trigger' => $messageBody,
                                'start_trigger' => $botReply->botFlow->start_trigger,
                                'trigger_type' => $triggerType
                            ]);

                            // Delete the current active flow
                            UserActiveFlow::where('phone_number', $contact->wa_id)->delete();
                            $activeFlow = null;
                        } elseif (!$activeFlow) {
                            // No active flow, this is a new flow start
                            Log::info('Message matches flow start trigger, no active flow', [
                                'user' => $contact->wa_id,
                                'new_flow_id' => $botReply->bot_flows__id,
                                'trigger' => $messageBody,
                                'start_trigger' => $botReply->botFlow->start_trigger,
                                'trigger_type' => $triggerType
                            ]);
                        }
                        break; // Exit the loop since we found a matching start trigger
                    }
                }
            }

            // If there's an active flow, only process replies from that flow
            // BUT allow welcome messages and non-flow messages to still work
            if ($activeFlow) {
                $allBotReplies = $allBotReplies->filter(function($reply) use ($activeFlow, $messageBody, $contact) {
                    // Always allow welcome messages
                    if ($reply->trigger_type == 'welcome') {
                        return true;
                    }

                    // Always allow new_message triggers (they have their own logic to check for active flows)
                    if ($reply->trigger_type == 'new_message') {
                        return true;
                    }

                    // If there's an active flow, only allow replies from that flow
                    if ($activeFlow && $reply->bot_flows__id) {
                        // If this reply belongs to a different flow, ignore it
                        if ($reply->bot_flows__id !== $activeFlow->flow_id) {
                            return false;
                        }

                        try {
                            // Get the last message sent to this contact
                            $lastMessage = $this->whatsAppMessageLogRepository->fetchIt([
                                'vendors__id' => $contact->vendors__id,
                                'contacts__id' => $contact->_id,
                                'type' => 'outgoing',
                            ], ['_id' => 'desc']);

                            // If the last message had buttons, this must be a button response
                            if ($lastMessage && isset($lastMessage->__data['interaction_message']['buttons'])) {
                                $buttons = $lastMessage->__data['interaction_message']['buttons'];
                                return collect($buttons)->contains(function($button) use ($messageBody) {
                                    return strtolower($button['title']) === strtolower($messageBody);
                                });
                            }
                        } catch (\Exception $e) {
                            Log::error('Error fetching last message for button validation', [
                                'error' => $e->getMessage(),
                                'contact_id' => $contact->_id
                            ]);
                        }

                        // For non-button responses, use trigger type matching
                        if ($reply->botFlow && $reply->botFlow->start_trigger) {
                            $triggerType = $reply->botFlow->trigger_type ?? 'is';
                            return $this->checkTriggerMatch($messageBody, $reply->botFlow->start_trigger, $triggerType);
                        }

                        // Fallback to exact match for non-flow replies
                        return strtolower($reply->reply_trigger) === strtolower($messageBody);
                    }

                    // If no active flow, allow both non-flow replies and flow start triggers
                    if (empty($reply->bot_flows__id)) {
                        // Non-flow reply
                        return true;
                    } else {
                        // Flow reply - check if it matches the start trigger or is a new_message trigger
                        if ($reply->botFlow) {
                            $triggerType = $reply->botFlow->trigger_type ?? 'is';

                            // Allow new_message triggers when there's no active flow
                            if ($triggerType === 'new_message') {
                                return true;
                            }

                            // For other trigger types, check if message matches the start trigger
                            if ($reply->botFlow->start_trigger) {
                                return $this->checkTriggerMatch($messageBody, $reply->botFlow->start_trigger, $triggerType);
                            }
                        }
                        return false;
                    }
                });

                Log::info('Bot replies after filtering', [
                    'user' => $contact->wa_id,
                    'message' => $messageBody,
                    'filtered_replies' => $allBotReplies->count(),
                    'active_flow' => $activeFlow ? $activeFlow->flow_id : 'none'
                ]);
            }
            // check if we already have incoming message 2 days
            $isIncomingMessageExists = $this->whatsAppMessageLogRepository->countIt([
                'vendors__id' => $contact->vendors__id,
                'contacts__id' => $contact->_id,
                'is_incoming_message' => 1,
                [
                    // send welcome message again after a day if there is no incoming response
                    'created_at', '>', now()->subHours(24)
                ],
            ]) > 1;
            foreach ($allBotReplies as $botReply) {
                Log::info('Processing bot reply', [
                    'user' => $contact->wa_id,
                    'bot_reply_id' => $botReply->_uid,
                    'trigger_type' => $botReply->trigger_type,
                    'reply_trigger' => $botReply->reply_trigger,
                    'flow_id' => $botReply->bot_flows__id ?? 'none'
                ]);

                // if time restrictions
                if ($isBotTimingsEnabled and !$isBotTimingsInTime and array_key_exists($botReply->trigger_type, $selectedOtherBotsForTimingRestrictions)) {
                    continue;
                }
                // get reply triggers
                $replyTriggers = strtolower($botReply->reply_trigger);
                if (!$testBotId and !in_array($botReply->trigger_type, ['welcome', 'new_message']) and $replyTriggers == '') {
                    continue;
                }
                // testing the bot reply
                if ($testBotId and ($testBotId != $botReply->_id)) {
                    continue;
                } elseif ($testBotId) {
                    $messageBody = $replyTriggers;
                }
                // create array of comma separated triggers
                $replyTriggers = array_filter(explode(',', $replyTriggers) ?? []);
                if ($testBotId or (empty($replyTriggers) and in_array($botReply->trigger_type, ['welcome', 'new_message']))) {
                    $replyTriggers = [
                        'dummy'
                    ];
                }
                foreach ($replyTriggers as $replyTrigger) {
                    $replyTrigger = trim($replyTrigger);
                    if (!$testBotId and !in_array($botReply->trigger_type, ['welcome', 'new_message']) and $replyTrigger == '') {
                        continue;
                    }
                    if ($testBotId) {
                        $messageBody = $replyTrigger;
                    }
                    // if not the test bot
                    if (!$testBotId) {
                        // if normal bot and it is inactive
                        if (!$botReply->botFlow and ($botReply->status == 2)) {
                            continue;
                        }
                        // if bot flow inactive
                        if ($botReply->botFlow?->status == 2) {
                            continue;
                        }
                    }

                    $replyText = null;
                    $isBotMatchedForThisReply = false;
                    $interactionMessageData = $botReply->__data['interaction_message'] ?? null;
                    $mediaMessageData = $botReply->__data['media_message'] ?? null;
                    if ($botReply->trigger_type == 'welcome') {
                        // For welcome triggers, check if this is a first-time contact
                        if (!$isIncomingMessageExists or $testBotId) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;

                            Log::info('Welcome trigger matched', [
                                'user' => $contact->wa_id,
                                'message' => $messageBody,
                                'bot_reply_id' => $botReply->_uid,
                                'flow_id' => $botReply->bot_flows__id ?? 'none',
                                'is_first_message' => !$isIncomingMessageExists
                            ]);
                        } else {
                            Log::info('Welcome trigger skipped - not first message', [
                                'user' => $contact->wa_id,
                                'message' => $messageBody,
                                'bot_reply_id' => $botReply->_uid,
                                'flow_id' => $botReply->bot_flows__id ?? 'none'
                            ]);
                        }
                    }
                    if ($botReply->trigger_type == 'start_promotional') {
                        if (Str::is($replyTrigger, $messageBody)) {
                            if ($contact->whatsapp_opt_out) {
                                $this->contactRepository->updateIt($contact, [
                                    'whatsapp_opt_out' => null
                                ]);
                            }
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'stop_promotional') {
                        if (Str::is($replyTrigger, $messageBody)) {
                            if (!$contact->whatsapp_opt_out) {
                                $this->contactRepository->updateIt($contact, [
                                    'whatsapp_opt_out' => 1
                                ]);
                            }
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'start_ai_bot') {
                        if (Str::is($replyTrigger, $messageBody)) {
                            if ($contact->disable_ai_bot) {
                                $this->contactRepository->updateIt($contact, [
                                    'disable_ai_bot' => 0
                                ]);
                            }
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'stop_ai_bot') {
                        if (Str::is($replyTrigger, $messageBody)) {
                            if (!$contact->disable_ai_bot) {
                                $this->contactRepository->updateIt($contact, [
                                    'disable_ai_bot' => 1
                                ]);
                            }
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'new_message') {
                        // New message trigger fires when user sends any message outside of active flows
                        // This should only trigger if there's no active flow for this contact
                        Log::info('Processing new_message trigger', [
                            'user' => $contact->wa_id,
                            'message' => $messageBody,
                            'bot_reply_id' => $botReply->_uid,
                            'flow_id' => $botReply->bot_flows__id ?? 'none',
                            'has_active_flow' => !is_null($activeFlow),
                            'active_flow_id' => $activeFlow ? $activeFlow->flow_id : null
                        ]);

                        if (!$activeFlow) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;

                            Log::info('New message trigger matched', [
                                'user' => $contact->wa_id,
                                'message' => $messageBody,
                                'bot_reply_id' => $botReply->_uid,
                                'flow_id' => $botReply->bot_flows__id ?? 'none'
                            ]);
                        } else {
                            Log::info('New message trigger skipped due to active flow', [
                                'user' => $contact->wa_id,
                                'active_flow_id' => $activeFlow->flow_id
                            ]);
                        }
                    } elseif ($botReply->bot_flows__id && $botReply->botFlow) {
                        // For flow bot replies, use the flow's start_trigger and trigger_type
                        $flowTriggerType = $botReply->botFlow->trigger_type ?? 'is';
                        $flowStartTrigger = $botReply->botFlow->start_trigger;

                        if ($this->checkTriggerMatch($messageBody, $flowStartTrigger, $flowTriggerType)) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;

                            Log::info('Flow bot reply matched using new trigger system', [
                                'user' => $contact->wa_id,
                                'message' => $messageBody,
                                'flow_id' => $botReply->bot_flows__id,
                                'trigger_type' => $flowTriggerType,
                                'start_trigger' => $flowStartTrigger
                            ]);
                        }
                    } elseif ($botReply->trigger_type == 'is') {
                        if (Str::is($replyTrigger, $messageBody)) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'starts_with') {
                        if (Str::startsWith($messageBody, $replyTrigger)) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'ends_with') {
                        if (Str::endsWith($messageBody, $replyTrigger)) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'contains_word') {
                        // Prepare the pattern to search for the whole word
                        // \b represents a word boundary in regex
                        $pattern = '/\b' . preg_quote($replyTrigger, '/') . '\b/u';
                        // Use preg_match to search the haystack for the whole word
                        if (preg_match($pattern, $messageBody) > 0) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    } elseif ($botReply->trigger_type == 'contains') {
                        if (Str::contains($messageBody, $replyTrigger)) {
                            $replyText = $botReply->reply_text;
                            $isBotMatchedForThisReply = true;
                        }
                    }
                    // if reply text is ready
                    if ($isBotMatchedForThisReply) {
                        $isBotMatched = true;

                        // Always try new node-based flow processing first (works for both new and converted legacy flows)
                        Log::info('Starting flow processing', [
                            'user' => $contact->wa_id,
                            'message' => $messageBody,
                            'bot_reply_id' => $botReply->_uid,
                            'flow_id' => $botReply->bot_flows__id ?? 'none'
                        ]);

                        $flowIntegrationService = new FlowIntegrationService();
                        $nodeFlowResult = $flowIntegrationService->processNodeBasedFlow($contact, $messageBody, $botReply, $options);

                        if ($nodeFlowResult && $nodeFlowResult['processed_by_new_flow']) {
                            Log::info('Processing node-based flow responses', [
                                'user' => $contact->wa_id,
                                'response_count' => count($nodeFlowResult['responses']),
                                'is_complete' => $nodeFlowResult['is_complete']
                            ]);

                            // Process responses from new flow system
                            foreach ($nodeFlowResult['responses'] as $response) {
                                // Initialize variables
                                $responseText = '';
                                $interactionData = null;
                                $mediaMessageData = null;

                                // Set response text if it exists
                                if (isset($response['text'])) {
                                    $responseText = $response['text'];
                                }

                                // Handle interactive responses
                                if ($response['type'] === 'interactive') {
                                    // Check if it has list_data (list type)
                                    if (isset($response['list_data'])) {
                                        // Format list data according to WhatsApp API requirements
                                        $sections = [];
                                        foreach ($response['list_data']['sections'] as $section) {
                                            $rows = [];
                                            foreach ($section['rows'] as $row) {
                                                $rows[] = [
                                                    'id' => (string)($row['id'] ?? $row['row_id'] ?? uniqid()),
                                                    'title' => $row['title'],
                                                    'description' => $row['description'] ?? null
                                                ];
                                            }
                                            
                                            if (!empty($rows)) {
                                                $sections[] = [
                                                    'title' => $section['title'],
                                                    'rows' => $rows
                                                ];
                                            }
                                        }

                                        $interactionData = [
                                            'body_text' => $responseText,
                                            'interactive_type' => 'list',
                                            'list_data' => [
                                                'button_text' => $response['list_data']['button_text'] ?? 'Select an option',
                                                'sections' => $sections
                                            ]
                                        ];

                                        Log::info('Sending WhatsApp list message', [
                                            'user' => $contact->wa_id,
                                            'sections_count' => count($sections),
                                            'total_rows' => array_sum(array_map(function($section) {
                                                return count($section['rows']);
                                            }, $sections)),
                                            'button_text' => $interactionData['list_data']['button_text']
                                        ]);

                                        Log::info('Sending interactive list message from node flow', [
                                            'user' => $contact->wa_id,
                                            'list_data' => $response['list_data'],
                                            'body_text' => $responseText,
                                            'sections_count' => count($response['list_data']['sections'] ?? [])
                                        ]);

                                        // Send interactive message directly using WhatsApp API
                                        $sendMessageResult = $this->whatsAppApiService->sendInteractiveMessage($contact->wa_id, $interactionData, $contact->vendors__id);
                                        $messageWamid = Arr::get($sendMessageResult, 'messages.0.id');
                                        
                                        if ($messageWamid) {
                                            $this->whatsAppMessageLogRepository->updateOrCreateWhatsAppMessageFromWebhook(
                                                getVendorSettings('current_phone_number_id', null, null, $contact->vendors__id),
                                                $contact->_id,
                                                $contact->vendors__id,
                                                $contact->wa_id,
                                                $messageWamid,
                                                'accepted',
                                                $sendMessageResult,
                                                $responseText,
                                                null,
                                                [],
                                                false,
                                                ['node_based_flow' => true]
                                            );
                                        }
                                        continue; // Skip the regular sendReplyBotMessage call
                                    }
                                    // Handle button type interactive
                                    elseif (isset($response['buttons'])) {
                                        $buttons = [];
                                        foreach ($response['buttons'] as $button) {
                                            $buttons[] = $button['title'];
                                        }

                                        $interactionData = [
                                            'body_text' => $responseText,
                                            'interactive_type' => 'button',
                                            'buttons' => $buttons,
                                            'header_text' => $response['header_text'] ?? '',
                                            'footer_text' => $response['footer_text'] ?? ''
                                        ];
                                        $responseText = ''; // Clear text as it's in body_text

                                        Log::info('Sending interactive button message', [
                                            'user' => $contact->wa_id,
                                            'buttons' => $buttons,
                                            'body_text' => $interactionData['body_text']
                                        ]);
                                    }
                                } elseif ($response['type'] === 'media_message') {
                                    // Handle media message responses
                                    $mediaMessageData = [
                                        'header_type' => $response['media_type'] ?? 'document',
                                        'media_link' => $response['media_url'] ?? '',
                                        'caption' => $response['caption'] ?? '',
                                        'file_name' => $response['filename'] ?? ''
                                    ];
                                    $responseText = ''; // Clear text as it's in caption

                                    Log::info('Processing media message from node-based flow', [
                                        'user' => $contact->wa_id,
                                        'media_type' => $mediaMessageData['header_type'],
                                        'media_url' => $mediaMessageData['media_link'],
                                        'caption' => $mediaMessageData['caption'],
                                        'node_id' => $response['node_id'] ?? 'unknown'
                                    ]);
                                }

                                // Prepare options for sending message
                                $messageOptions = [
                                    'mediaMessageData' => $mediaMessageData,
                                    'from_phone_number_id' => $options['fromPhoneNumberId'],
                                    'node_based_flow' => true
                                ];

                                // Only include messageWamid for non-message type responses (interactive, media)
                                // This ensures direct messages for basic text responses
                                if ($response['type'] !== 'message') {
                                    $messageOptions['messageWamid'] = $options['messageWamid'];
                                }

                                // Send the response
                                $this->sendReplyBotMessage($contact->_uid, $responseText, $contact->vendors__id, $interactionData, $messageOptions);
                            }

                            if ($testBotId) {
                                return $this->engineSuccessResponse([], __tr('Node-based flow processed successfully'));
                            }

                            // Skip all legacy processing since new flow system handled it
                            return;
                        }

                        // If this bot reply belongs to a flow but new flow system didn't process it,
                        // it means the message is not a valid flow start trigger, so skip processing
                        if ($botReply->bot_flows__id) {
                            Log::info('Skipping flow processing - not a valid start trigger', [
                                'user' => $contact->wa_id,
                                'message' => $messageBody,
                                'flow_id' => $botReply->bot_flows__id,
                                'reply_trigger' => $botReply->reply_trigger
                            ]);
                            break; // Skip this bot reply and continue to next one
                        }

                        // Clear any existing active flows before processing new reply
                        // UserActiveFlow::where('phone_number', $contact->wa_id)->delete();
                        
                        // Check if this is a flow start trigger (ONLY start_trigger)
                        $isFlowStartTrigger = false;
                        if ($botReply->bot_flows__id) {
                            // Get the bot flow
                            $botFlow = \App\Yantrana\Components\BotReply\Models\BotFlowModel::find($botReply->bot_flows__id);
                            if ($botFlow) {
                                // Check if message matches the flow start_trigger based on trigger type
                                $triggerType = $botFlow->trigger_type ?? 'is';

                                Log::info('Checking flow start trigger match', [
                                    'message' => $messageBody,
                                    'start_trigger' => $botFlow->start_trigger,
                                    'trigger_type' => $triggerType,
                                    'flow_id' => $botFlow->_uid,
                                    'flow_title' => $botFlow->title ?? 'unknown'
                                ]);

                                $isFlowStartTrigger = $this->checkTriggerMatch($messageBody, $botFlow->start_trigger, $triggerType);

                                if ($isFlowStartTrigger) {
                                    Log::info('Message matches flow start_trigger in WhatsApp engine', [
                                        'message' => $messageBody,
                                        'start_trigger' => $botFlow->start_trigger,
                                        'trigger_type' => $triggerType,
                                        'flow_id' => $botFlow->_uid
                                    ]);
                                } else {
                                    Log::info('Message does not match flow start_trigger in WhatsApp engine', [
                                        'message' => $messageBody,
                                        'start_trigger' => $botFlow->start_trigger ?? 'not_set',
                                        'trigger_type' => $triggerType,
                                        'flow_id' => $botFlow->_uid
                                    ]);
                                }
                            }
                        }

                        // If this is a flow's start trigger, set it as active
                        if ($botReply->bot_flows__id && ($isFlowStartTrigger || $testBotId)) {
                            Log::info('Setting active flow', [
                                'user' => $contact->wa_id,
                                'flow_id' => $botReply->bot_flows__id,
                                'trigger' => $messageBody,
                                'reply_trigger' => $botReply->reply_trigger,
                                'test_bot' => (bool)$testBotId,
                                'is_start_trigger' => $isFlowStartTrigger,
                                'clearing_existing_flow' => !is_null($activeFlow)
                            ]);

                            try {
                                // Start transaction for flow state changes
                                DB::beginTransaction();

                                // Clear any existing active flow when starting a new one
                                if ($activeFlow) {
                                    UserActiveFlow::where('phone_number', $contact->wa_id)->delete();
                                }

                                // Set the new active flow
                                $activeFlow = UserActiveFlow::setActiveFlow(
                                    $contact->_id,
                                    $botReply->bot_flows__id,
                                    $contact->wa_id
                                );
                                
                                // Initialize flow state if needed
                                if (isset($botReply->__data['flow_init_state'])) {
                                    $activeFlow->__data = array_merge(
                                        $activeFlow->__data ?? [],
                                        ['flow_state' => $botReply->__data['flow_init_state']]
                                    );
                                    $activeFlow->save();
                                }
                                
                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Log::error('Failed to initialize flow', [
                                    'error' => $e->getMessage(),
                                    'user' => $contact->wa_id,
                                    'flow_id' => $botReply->bot_flows__id
                                ]);
                            }
                        }
                        
                            // If this is the last reply in a flow (no next steps), clear the active flow
                            if ($activeFlow && $botReply->bot_flows__id == $activeFlow->flow_id) {
                                $hasNextSteps = false;
                                $nextStepType = 'none';
                                
                                // Check for buttons in interaction message
                                if (isset($botReply->__data['interaction_message']['buttons']) && 
                                    !empty($botReply->__data['interaction_message']['buttons'])) {
                                    $hasNextSteps = true;
                                    $nextStepType = 'buttons';
                                }

                                // Check for expected responses in flow data
                                if (isset($botReply->__data['expected_responses']) && 
                                    !empty($botReply->__data['expected_responses'])) {
                                    $hasNextSteps = true;
                                    $nextStepType = 'expected_responses';
                                }
                                
                                if ($hasNextSteps) {
                                    Log::info('Flow continuing - has next steps', [
                                        'user' => $contact->wa_id,
                                        'flow_id' => $activeFlow->flow_id,
                                        'next_step_type' => $nextStepType
                                    ]);
                                }
                                
                                // If no next steps or this is a terminal node, end the flow
                                if (!$hasNextSteps || isset($botReply->__data['is_terminal_node'])) {
                                    Log::info('Ending flow - terminal node or no next steps', [
                                        'user' => $contact->wa_id,
                                        'flow_id' => $activeFlow->flow_id,
                                        'last_reply_id' => $botReply->_id,
                                        'next_step_type' => $nextStepType,
                                        'is_terminal' => isset($botReply->__data['is_terminal_node'])
                                    ]);
                                    
                                    // Delete the active flow since this is the last node
                                    UserActiveFlow::where('phone_number', $contact->wa_id)->delete();
                                    
                                    // Trigger any completion callbacks if defined
                                    if (isset($botReply->__data['completion_callback'])) {
                                        try {
                                            $callback = $botReply->__data['completion_callback'];
                                            if (is_callable($callback)) {
                                                call_user_func($callback, $contact, $activeFlow);
                                            }
                                        } catch (\Exception $e) {
                                            Log::error('Flow completion callback failed', [
                                                'error' => $e->getMessage(),
                                                'flow_id' => $activeFlow->flow_id
                                            ]);
                                        }
                                    }
                                } else {
                                    Log::info('Flow continuing - has next steps', [
                                        'user' => $contact->wa_id,
                                        'flow_id' => $activeFlow->flow_id,
                                        'next_step_type' => $nextStepType
                                    ]);
                                }
                            }

                        if ($replyText) {
                            $replyText = $this->dynamicValuesReplacement($replyText, $contact);
                        }
                        // if interaction message
                        if ($interactionMessageData) {
                            // body text
                            $interactionMessageData['body_text'] = $replyText;
                            // header text assignments
                            if ($interactionMessageData['header_text']) {
                                $interactionMessageData['header_text'] = $this->dynamicValuesReplacement($interactionMessageData['header_text'], $contact);
                            }
                            // footer text assignments
                            if ($interactionMessageData['footer_text']) {
                                $interactionMessageData['footer_text'] = $this->dynamicValuesReplacement($interactionMessageData['footer_text'], $contact);
                            }
                        } elseif ($mediaMessageData) {
                            // caption text
                            $mediaMessageData['caption'] = $this->dynamicValuesReplacement($mediaMessageData['caption'], $contact);
                        }
                        $sendReplyBotMessageResponse = $this->sendReplyBotMessage($contact->_uid, $replyText, $contact->vendors__id, $interactionMessageData, [
                            'mediaMessageData' => $mediaMessageData,
                            'from_phone_number_id' => $options['fromPhoneNumberId'],
                            'messageWamid' => $options['messageWamid'],
                        ]);
                        if ($testBotId) {
                            return $sendReplyBotMessageResponse;
                        }
                        break;
                    }
                }
            }
        }
        if ($testBotId) {
            return $this->engineFailedResponse([], __tr('Bot Validation Failed due to unmatched'));
        }
        // if time restriction
        if ($isBotTimingsEnabled and !$isBotTimingsInTime and $isAiBotTimingsEnabled) {
            return false;
        }
        // initial ai bot only if manual bot didn't replied
        // ai bot is enabled
        // bot url has been set
        // contact ai bot replies is not disabled
        // has in subscription plan
        $vendorPlanDetails = vendorPlanDetails('ai_chat_bot', 0, $contact->vendors__id);
        if (!$isBotMatched and $vendorPlanDetails['is_limit_available'] and !$contact->disable_ai_bot) {
            $aiBotReplyText = null;
            // open ai
            if (!$aiBotReplyText and getVendorSettings('enable_open_ai_bot', null, null, $contact->vendors__id) and getVendorSettings('open_ai_access_key', null, null, $contact->vendors__id)) {
                try {
                    $aiBotReplyText = app()->make(OpenAiService::class)->generateAnswerFromMultipleSections($messageBody, $contact->_uid, $contact->vendors__id);
                    // check if got the reply
                    if ($aiBotReplyText) {
                        $botName = getVendorSettings('open_ai_bot_name', null, null, $contact->vendors__id);
                        $aiBotReplyText =  $botName ? ($botName . ":\n\n" . $aiBotReplyText) : $aiBotReplyText ;
                        $this->sendReplyBotMessage($contact->_uid, $aiBotReplyText, $contact->vendors__id, null, [
                            'ai_bot_reply' => true,
                            'open_ai_reply' => true,
                            'from_phone_number_id' => $options['fromPhoneNumberId'],
                            'messageWamid' => $options['messageWamid'],
                        ]);
                    }
                } catch (\Throwable $e) {
                    __logDebug($e->getMessage());
                    // send error message to the customers
                    if (getVendorSettings('open_ai_failed_message', null, null, $contact->vendors__id)) {
                        $this->sendReplyBotMessage($contact->_uid, getVendorSettings('open_ai_failed_message', null, null, $contact->vendors__id), $contact->vendors__id, null, [
                            'ai_error_triggered' => true,
                            'from_phone_number_id' => $options['fromPhoneNumberId'],
                            'messageWamid' => $options['messageWamid'],
                        ]);
                    }
                }
            }
            // flowise ai
            if (!$aiBotReplyText and getVendorSettings('enable_flowise_ai_bot', null, null, $contact->vendors__id) and getVendorSettings('flowise_url', null, null, $contact->vendors__id)) {
                try {
                    // base request start
                    $botRequest = Http::throw(function ($response, $e) {
                        __logDebug($e->getMessage());
                    });
                    // set the token if required
                    if ($bearerToken = getVendorSettings('flowise_access_token', null, null, $contact->vendors__id)) {
                        $botRequest->withToken($bearerToken);
                    }
                    $aiBotReplyText = $botRequest->post(getVendorSettings('flowise_url', null, null, $contact->vendors__id), [
                        'question' => $messageBody,
                        'overrideConfig' => [
                            'sessionId' => $contact->_uid
                        ],
                    ])->json('text');
                    // check if got the reply
                    if ($aiBotReplyText) {
                        $this->sendReplyBotMessage($contact->_uid, $aiBotReplyText, $contact->vendors__id, null, [
                            'ai_bot_reply' => true,
                            'open_flowise_ai_reply' => true,
                            'from_phone_number_id' => $options['fromPhoneNumberId'],
                            'messageWamid' => $options['messageWamid'],
                        ]);
                    }
                } catch (\Throwable $e) {
                    __logDebug($e->getMessage());
                    // send error message to the customers
                    if (getVendorSettings('flowise_failed_message', null, null, $contact->vendors__id)) {
                        $this->sendReplyBotMessage($contact->_uid, getVendorSettings('flowise_failed_message', null, null, $contact->vendors__id), $contact->vendors__id, null, [
                            'ai_error_triggered' => true,
                            'from_phone_number_id' => $options['fromPhoneNumberId'],
                            'messageWamid' => $options['messageWamid'],
                        ]);
                    }
                }
            }
        }
    }
    /**
     * Formate json data to display
     *
     * @param string $data
     * @param integer $indentLevel
     * @return string
     */
    public function jsonToListRecursive($data, $indentLevel = 0)
    {
        if (is_string($data)) {
            $decodedData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return "Invalid JSON format.";
            }
        } else {
            $decodedData = $data;
        }
        // Initialize an empty string to hold the list output
        $listOutput = '';
        // Create indentation for nested levels
        $indent = str_repeat('&nbsp;', $indentLevel * 4);
        // Iterate over each key-value pair to build the list format
        foreach ($decodedData as $key => $value) {
            // If the value is an array or object, call the function recursively to list nested data
            if (is_array($value)) {
                $listOutput .= $indent . $key . ":<br>";
                $listOutput .= $this->jsonToListRecursive($value, $indentLevel + 1);
            } else {
                // For simple key-value pairs, append each item to the list output
                $listOutput .= $indent . $key . ': ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "<br>";
            }
        }
        return $listOutput;
    }
    /**
     * Webhook for message handling
     *
     * @param Request $request
     * @param string $vendorUid
     * @return void
     */
    public function processWebhook($request, $vendorUid)
    {
        $vendorId = getPublicVendorId($vendorUid);
        if (! $vendorId) {
            return false;
        }
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $vendorId);
        if (!$vendorPlanDetails->hasActivePlan()) {
            abort(403, 'no active subscription');
        }
        $messageEntry = $request->get('entry');
        $phoneNumberId = Arr::get($messageEntry, '0.changes.0.value.metadata.phone_number_id');
        $messageStatusObject = Arr::get($messageEntry, '0.changes.0.value.statuses');
        $messageObject = Arr::get($messageEntry, '0.changes.0.value.messages');
        $messageStatus = null;
        $contactStatus = 'existing';
        // set the webhook messages field as configured if not already done
        if (!getVendorSettings('webhook_messages_field_verified_at', null, null, $vendorUid)
           and (Arr::get($messageEntry, '0.changes.0.field') == 'messages')) {
            $this->vendorSettingsEngine->updateProcess('whatsapp_cloud_api_setup', [
                'webhook_messages_field_verified_at' => now()
            ], $vendorId);
            // messages
            updateModelsViaVendorBroadcast($vendorUid, [
                'isWebhookMessagesFieldVerified' => true
            ]);
            // if its test message notification then get back
            if (Arr::get($messageObject, '0.text.body') == 'this is a text message') {
                return false;
            }
        }
        $mediaData = null;
        $waId = null;
        $contactUid = null;
        $campaignUid = null;
        $messageWamid = null;
        // mainly for incoming message
        $messageBody = null;
        $isNewIncomingMessage = false;
        $repliedToWamid = null;
        if ($messageStatusObject) {
            $waId = Arr::get($messageStatusObject, '0.recipient_id'); // recipient
            $messageWamid = Arr::get($messageStatusObject, '0.id');
            $messageStatus = Arr::get($messageStatusObject, '0.status');
            $timestamp = Arr::get($messageStatusObject, '0.timestamp');
            $contact = $this->contactRepository->getVendorContactByWaId($waId, $vendorId);
            if (__isEmpty($contact)) {
                return false;
            }
            $contactUid = $contact->_uid;
            // Update Record for sent message
            $this->whatsAppMessageLogRepository->updateOrCreateWhatsAppMessageFromWebhook(
                $phoneNumberId,
                $contact->_id,
                $vendorId,
                $waId,
                $messageWamid,
                $messageStatus,
                $messageEntry,
                null,
                $timestamp,
                null,
                true // do not create new record if not found
            );
        }
        // incoming message
        elseif ($messageObject) {
            // verify the phone number id assigned with is account
            $findPhoneNumber = Arr::first((getVendorSettings('whatsapp_phone_numbers', null, null, $vendorId) ?: []), function ($value, $key) use (&$phoneNumberId) {
                return $value['id'] == $phoneNumberId;
            });
            if (getVendorSettings('current_phone_number_id', null, null, $vendorId) != $phoneNumberId) {
                if (empty($findPhoneNumber)) {
                    return false;
                }
            }
            $waId = Arr::get($messageObject, '0.from');
            $messageWamid = Arr::get($messageObject, '0.id');
            $messageType = Arr::get($messageObject, '0.type');
            // welcome trigger
            if ($messageType == 'request_welcome') {
                return false;
            }
            // deleted message
            /**
             * @link https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks/payload-examples#status--message-deleted
             */
            if (Arr::get($messageObject, '0.errors.code') == 131051) {
                return false;
            }
            $messageBody = null;
            $isNewIncomingMessage = true;
            $mediaData = [];
            if (in_array($messageType, [
                'text',
            ])) {
                $messageBody = Arr::get($messageObject, '0.text.body');
            } elseif (in_array($messageType, [
               'interactive',
            ])) {
                $messageBody = Arr::get($messageObject, '0.interactive.button_reply.title');
                if (!$messageBody) {
                    $messageBody = Arr::get($messageObject, '0.interactive.list_reply.title');
                }
                if (!$messageBody) {
                    $messageBody = $this->jsonToListRecursive(Arr::get($messageObject, '0.interactive.nfm_reply.response_json'));
                }
            } elseif (in_array($messageType, [
                'button',
            ])) {
                $messageBody = Arr::get($messageObject, '0.button.text');
            } elseif (in_array($messageType, [
                'image',
                'video',
                'audio',
                'document',
            ])) {
                $downloadedFileInfo = $this->mediaEngine->downloadAndStoreMediaFile($this->whatsAppApiService->downloadMedia(Arr::get($messageObject, "0.$messageType.id"), $vendorId), $vendorUid, $messageType);
                $mediaData = [
                    'type' => $messageType,
                    'link' => Arr::get($downloadedFileInfo, 'path'),
                    'caption' => Arr::get($messageObject, "0.$messageType.caption"),
                    'mime_type' => Arr::get($messageObject, "0.$messageType.mime_type"),
                    'file_name' => Arr::get($downloadedFileInfo, 'fileName'),
                    'original_filename' => Arr::get($downloadedFileInfo, 'fileName'),
                ];
            } elseif ($messageType === 'order') {
                // Handle catalog order messages
                $this->processCatalogOrderMessage($messageObject[0], $waId, $vendorId);
                $messageBody = 'Order placed from catalog';
            }
            $timestamp = Arr::get($messageObject, '0.timestamp');
            $reactionMessage = Arr::get($messageObject, '0.reaction.emoji');
            if ($reactionMessage) {
                $repliedToMessage = $repliedToWamid = Arr::get($messageObject, '0.reaction.message_id');
                $messageBody = $reactionMessage;
            } else {
                // replied message
                $repliedToMessage = $repliedToWamid = Arr::get($messageObject, '0.context.id');
            }
            if ($repliedToMessage) {
                $repliedToMessage = $this->whatsAppMessageLogRepository->fetchIt([
                    'wamid' => $repliedToMessage,
                    'vendors__id' => $vendorId,
                ]);
                if (! __isEmpty($repliedToMessage)) {
                    $repliedToMessage = $repliedToMessage->_uid;
                } else {
                    $repliedToMessage = null;
                }
            }
            $isForwarded = Arr::get($messageObject, '0.context.forwarded');
            $contact = $this->contactRepository->getVendorContactByWaId($waId, $vendorId);
            if (!__isEmpty($contact)) {
                // update contact name
                if (!$contact->first_name) {
                    $profileName = Arr::get($messageEntry, '0.changes.0.value.contacts.0.profile.name');
                    $firstName = Arr::get(explode(' ', $profileName), '0');
                    $contact = $this->contactRepository->updateIt($contact, [
                        'first_name' => $firstName,
                        'last_name' => str_replace($firstName, ' ', $profileName),
                    ], $vendorId);
                    $contactStatus = 'updated';
                }
            } else {
                // check the feature limit
                $vendorPlanDetails = vendorPlanDetails('contacts', $this->contactRepository->countIt([
                    'vendors__id' => $vendorId
                ]), $vendorId);
                if (!$vendorPlanDetails['is_limit_available']) {
                    return false;
                }
                $profileName = Arr::get($messageEntry, '0.changes.0.value.contacts.0.profile.name');
                $firstName = Arr::get(explode(' ', $profileName), '0');
                $contact = $this->contactRepository->storeContact([
                    'first_name' => $firstName,
                    'last_name' => str_replace($firstName, ' ', $profileName),
                    'phone_number' => $waId,
                ], $vendorId);
                $contactStatus = 'new';
            }
            $contactUid = $contact->_uid;
            $hasLogEntryOfMessage = false;
            if ($messageWamid) {
                $hasLogEntryOfMessage = $this->whatsAppMessageLogRepository->countIt([
                    'wamid' => $messageWamid,
                    'vendors__id' => $vendorId,
                ]);
            }
            // prevent repeated message creation
            if ($hasLogEntryOfMessage) {
                return false;
            }
            // create Record for sent message
            $this->whatsAppMessageLogRepository->storeIncomingMessage(
                $phoneNumberId,
                $contact->_id,
                $vendorId,
                $waId, // sender
                $messageWamid,
                $messageEntry,
                $messageBody,
                $timestamp,
                $mediaData,
                $repliedToMessage,
                $isForwarded
            );
        }
        if ($messageWamid) {
            $messageLogEntry = $this->whatsAppMessageLogRepository->fetchIt([
                'wamid' => $messageWamid,
                'vendors__id' => $vendorId,
            ]);
            // get the campaign if required
            if (!__isEmpty($messageLogEntry) and $messageLogEntry->campaigns__id) {
                $campaign = $this->campaignRepository->fetchIt([
                    'vendors__id' => $vendorId,
                    '_id' => $messageLogEntry->campaigns__id,
                ]);
                if (!__isEmpty($campaign)) {
                    $campaignUid = $campaign->_uid;
                }
            }
        }

        if ($contactUid) {
            $contact = $this->contactRepository->with('lastMessage')->getVendorContactByWaId($waId, $vendorId);
            // Dispatch event for message
            event(new VendorChannelBroadcast($vendorUid, [
                'message_status' => $messageStatus ?? null,
                'contactUid' => $contactUid,
                'isNewIncomingMessage' => $isNewIncomingMessage,
                'campaignUid' => $campaignUid,
                'lastMessageUid' => $contact->lastMessage?->_uid,
                'assignedUserId' => $contact->assigned_users__id,
                'formatted_last_message_time' => $contact->lastMessage?->formatted_message_time,
            ]));

            if ($messageBody) {
                fromPhoneNumberIdForRequest($phoneNumberId);

                // Handle order flow interactions first
                if ($this->handleOrderFlowInteractions($contact, $messageBody, $messageType, $messageObject[0] ?? [], $vendorId)) {
                    // Order flow handled, skip bot processing
                } else {
                    // process the bot if needed any
                    $this->processReplyBot($contact, $messageBody, null, [
                        'fromPhoneNumberId' => $phoneNumberId,
                        'messageWamid' => $messageWamid,
                    ]);
                }
            }
            // call webhook
            dispatchVendorWebhook($vendorId, [
                'contact' => array_merge([
                    'status' => $contactStatus,
                    'phone_number' => $contact->wa_id,
                    'uid' => $contact->_uid,
                ], $contact->only([
                    'first_name',
                    'last_name',
                    'email',
                    'language_code',
                ]), [
                    'country' => $contact->country?->name,
                ]),
                'message' => [
                    'whatsapp_business_phone_number_id' => $phoneNumberId ?? null,
                    'whatsapp_message_id' => $messageWamid ?? null,
                    'replied_to_whatsapp_message_id' => $repliedToWamid,
                    'is_new_message' => $isNewIncomingMessage,
                    'body' => $messageBody,
                    'status' => $messageStatus ?? null,
                    'media' => $mediaData,
                ],
                'whatsapp_webhook_payload' => $request->all()
            ]);
        }
        return true;
    }
    /**
     * Update the unread count via client model updates
     *
     * @return EngineResponse
     */
    public function updateUnreadCount()
    {
        $updateData = [
            'unreadMessagesCount' => 0,
            'myUnassignedUnreadMessagesCount' => 0,
            'myAssignedUnreadMessagesCount' => $this->whatsAppMessageLogRepository->getMyAssignedUnreadMessagesCount()
        ];
        if (isVendorAdmin(getVendorId()) or !hasVendorAccess('assigned_chats_only')) {
            $updateData['unreadMessagesCount'] = $this->whatsAppMessageLogRepository->getUnreadCount();
            $updateData['myUnassignedUnreadMessagesCount'] = $this->whatsAppMessageLogRepository->getMyAssignedUnreadMessagesCount(null, null, null);
        } else {
            $updateData['unreadMessagesCount'] = $updateData['myAssignedUnreadMessagesCount'];
        }
        updateClientModels($updateData);
        return $this->engineSuccessResponse([]);
    }

    /**
     * Update the unread count via client model updates
     *
     * @return EngineResponse
     */
    public function refreshHealthStatus()
    {
        $healthStatus = $this->whatsAppApiService->healthStatus();
        $whatsAppBusinessAccountId = getVendorSettings('whatsapp_business_account_id');
        if (!$whatsAppBusinessAccountId) {
            return $this->engineFailedResponse([], __tr('WhatsApp Business Account ID not found'));
        }
        $now = now();
        $healthData = [
            'whatsapp_health_status_data' => [
                $whatsAppBusinessAccountId => [
                    'whatsapp_business_account_id' => $whatsAppBusinessAccountId,
                    'health_status_updated_at' => $now,
                    'health_status_updated_at_formatted' => formatDateTime($now),
                    'health_data' => $healthStatus,
                ]
            ]
        ];
        // store information
        $this->vendorSettingsEngine->updateProcess('internals', $healthData);
        // update models
        updateClientModels([
            'healthStatusData' => $healthData['whatsapp_health_status_data'][$whatsAppBusinessAccountId]
        ]);
        return $this->engineSuccessResponse([], __tr('WhatsApp Business Health Data Refreshed'));
    }
    /**
     * Update the unread count via client model updates
     *
     * @return EngineResponse
     */
    public function processSyncPhoneNumbers()
    {
        $phoneNumbers = $this->whatsAppApiService->phoneNumbers()['data'] ?? [];
        if (empty($phoneNumbers)) {
            return $this->engineFailedResponse([], __tr('Phone numbers not available'));
        }
        $whatsAppBusinessAccountId = getVendorSettings('whatsapp_business_account_id');
        if (!$whatsAppBusinessAccountId) {
            return $this->engineFailedResponse([], __tr('WhatsApp Business Account ID not found'));
        }
        $vendorId = getVendorId();
        $phoneNumberRecord = Arr::first(($phoneNumbers ?? []), function ($value, $key) {
            return $value['id'] == getVendorSettings('current_phone_number_id');
        });
        if (!$phoneNumberRecord) {
            $phoneNumberRecord = $phoneNumbers[0];
        }
        if (!$this->vendorSettingsEngine->updateProcess('whatsapp_cloud_api_setup', [
            'whatsapp_phone_numbers' => $phoneNumbers,
            'current_phone_number_number' => cleanDisplayPhoneNumber($phoneNumberRecord['display_phone_number']),
            'current_phone_number_id' => $phoneNumberRecord['id'],
        ], $vendorId)) {
            return $this->engineFailedResponse(['show_message' => true], __tr('Failed to update Phone Numbers.'));
        };
        // update models
        updateClientModels([
            'whatsAppPhoneNumbers' => $phoneNumbers
        ]);
        return $this->engineSuccessResponse([], __tr('WhatsApp Business Phone Numbers Synced'));
    }

    /**
     * Format the message like whatsapp do
     *
     * @param string $text
     * @return string
     */
    protected function formatWhatsAppText($text)
    {
        return formatWhatsAppText($text);

    }

    public function setupWhatsAppEmbeddedSignUpProcess($request)
    {
        $processedResponse = $this->whatsAppConnectApiService->processEmbeddedSignUp($request);
        if ($processedResponse->success()) {
            $this->refreshHealthStatus();
        }
        return $processedResponse;
    }
    /**
     * Disconnect Account
     *
     * @return EngineResponse
     */
    public function processDisconnectAccount($vendorId = null)
    {
        $vendorId  = $vendorId ?: getVendorId();
        if (!isWhatsAppBusinessAccountReady($vendorId)) {
            return $this->engineFailedResponse([], __tr('Account should be ready in order to disconnect.'));
        }
        // remove webhooks
        if (!getVendorSettings('embedded_setup_done_at', null, null, $vendorId)) {
            $this->processDisconnectWebhook($vendorId);
        } else {
            $this->whatsAppConnectApiService->removeExistingWebhooks(getVendorSettings('whatsapp_business_account_id', null, null, $vendorId), getVendorSettings('whatsapp_access_token', null, null, $vendorId));
        }
        if ($this->vendorSettingsEngine->deleteItemProcess([
            'embedded_setup_done_at',
            'facebook_app_id',
            'facebook_app_secret',
            'whatsapp_access_token',
            'whatsapp_business_account_id',
            'current_phone_number_number',
            'current_phone_number_id',
            'webhook_verified_at',
            'webhook_messages_field_verified_at',
            'whatsapp_phone_numbers_data',
            'whatsapp_onboarding_raw_data',
        ], $vendorId)->success()) {
            return $this->engineSuccessResponse([], __tr('Account has been disconnected successfully'));
        }
        return $this->engineFailedResponse([], __tr('Failed to disconnect account'));
    }
    /**
     * Disconnect Webhook
     *
     * @return EngineResponse
     */
    public function processDisconnectWebhook($vendorId = null)
    {
        $vendorId  = $vendorId ?: getVendorId();
        if (!getVendorSettings('facebook_app_secret', null, null, $vendorId)) {
            return $this->engineFailedResponse([], __tr('Missing App Secret'));
        }
        $processedResponse = $this->whatsAppConnectApiService->disconnectBaseWebhook(getVendorSettings('facebook_app_id', null, null, $vendorId), getVendorSettings('facebook_app_secret', null, null, $vendorId), getVendorSettings('whatsapp_business_account_id', null, null, $vendorId));
        if (isset($processedResponse['success']) and $processedResponse['success']) {
            if ($this->vendorSettingsEngine->deleteItemProcess([
                'webhook_verified_at',
                'webhook_messages_field_verified_at',
            ], $vendorId)->success()) {
                // messages
                updateClientModels([
                    'isWebhookMessagesFieldVerified' => false,
                    'isWebhookVerified' => false,
                ]);
                return $this->engineSuccessResponse([], __tr('Webhook Disconnected'));
            }
        }
        return $this->engineFailedResponse([], __tr('Nothing to disconnect'));
    }
    /**
     * Connect Webhook
     *
     * @return void
     */
    public function processConnectWebhook()
    {
        $vendorUid = getVendorUid();
        $processedResponse = $this->whatsAppConnectApiService->connectBaseWebhook(getVendorSettings('facebook_app_id'), getVendorSettings('facebook_app_secret'), $vendorUid);
        // Note: We use override webhook urls only for embedded signup
        // and as if vendor connected via Embedded signup they don't have facility to disconnect webhooks
        // thats why commented out the url
        // $processedResponse = $this->whatsAppConnectApiService->connectWebhookOverrides($vendorUid, getVendorSettings('whatsapp_business_account_id'));
        if (isset($processedResponse['success']) and $processedResponse['success']) {
            return $this->engineSuccessResponse([], __tr('Webhook Connected'));
        }
        return $this->engineFailedResponse([], __tr('Nothing to connect'));
    }

    /**
     * Requeue the failed messages
     *
     * @param BaseRequestTwo $request
     * @param sting $campaignUid
     * @return EngineResponse
     */
    public function processRequeueFailedMessages($request, $campaignUid)
    {

        $campaign = $this->campaignRepository->fetchIt([
            '_uid' => $campaignUid,
            'vendors__id' => getVendorId(),
            'status' => 1,
        ]);

        if (__isEmpty($campaign)) {
            return $this->engineFailedResponse([], __tr('No active campaign found'));
        }

        if ($this->whatsAppMessageQueueRepository->updateItAll([
            'campaigns__id' => $campaign->_id
        ], [
            'status' => 1
        ])) {
            return $this->engineSuccessResponse([], __tr('Requeued the failed messages'));
        };

        return $this->engineFailedResponse([], __tr('Nothing to requeue'));
    }

    /**
     * Request Business Profile Information
     *
     * @param int $phoneNumberId
     * @return EngineResponse
     */
    public function requestBusinessProfile($phoneNumberId)
    {
        $businessProfile = $this->whatsAppApiService->businessProfile($phoneNumberId);
        return $this->engineSuccessResponse([
            'phoneNumberId' => $phoneNumberId,
            'businessProfile' => $businessProfile['data'][0] ?? []
        ]);
    }

    /**
     * Update WhatsApp Business Number Profile
     *
     * @param BaseRequestTwo $request
     * @return EngineResponse
     */
    public function requestUpdateBusinessProfile($request)
    {
        $dataToUpdate = array_filter($request->only([
          'address',
          'description',
          'vertical',
          'about',
          'email',
          'websites',
        ]));
        if ($request->uploaded_media_file_name) {
            $resumableUploadFileId = $this->whatsAppApiService->uploadResumableMedia($request->uploaded_media_file_name, [
                'binary' => true
            ]);
            $dataToUpdate['profile_picture_handle'] = $resumableUploadFileId;
        }
        $businessProfile = $this->whatsAppApiService->updateBusinessProfile($request->phoneNumberId, $dataToUpdate);
        if ($businessProfile['success'] ?? null) {
            return $this->engineSuccessResponse([], __tr('Business Profile Updated'));
        }
        return $this->engineFailedResponse([], __tr('Failed to update Business Profile'));
    }

    protected function isInAllowedBotTiming($vendorId)
    {
        if (getVendorSettings('enable_bot_timing_restrictions', null, null, $vendorId)) {
            $now = Carbon::now(getVendorSettings('bot_timing_timezone'));
            $botStartTime = Carbon::createFromFormat('H:i', getVendorSettings('bot_start_timing', null, null, $vendorId), getVendorSettings('bot_timing_timezone', null, null, $vendorId));
            $botEndTime = Carbon::createFromFormat('H:i', getVendorSettings('bot_end_timing', null, null, $vendorId), getVendorSettings('bot_timing_timezone', null, null, $vendorId));
            if ($botEndTime->lte($botStartTime)) {
                $botEndTime = $botEndTime->addDay();
            }
            if ($now->between($botStartTime, $botEndTime)) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Process catalog order message
     *
     * @param array $orderMessage
     * @param string $customerPhone
     * @param int $vendorId
     * @return void
     */
    protected function processCatalogOrderMessage(array $orderMessage, string $customerPhone, int $vendorId): void
    {
        try {
            // Check if order processing is enabled
            if (!getVendorSettings('enable_whatsapp_orders', false, null, $vendorId)) {
                return;
            }

            $orderData = $orderMessage['order'] ?? [];

            if (empty($orderData['product_items'])) {
                Log::warning('Empty order items received', [
                    'customer_phone' => $customerPhone,
                    'vendor_id' => $vendorId,
                ]);
                return;
            }

            // Process the order using the order service
            $orderService = app(\App\Yantrana\Components\WhatsAppService\Services\WhatsAppOrderService::class);
            $result = $orderService->processCatalogOrder($orderData, $customerPhone, $vendorId);

            if (!$result['success']) {
                // Send error message to customer
                $this->whatsAppApiService->sendMessage(
                    $customerPhone,
                    "❌ Sorry, we couldn't process your order. Please try again or contact support.",
                    $vendorId
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to process catalog order message', [
                'error' => $e->getMessage(),
                'customer_phone' => $customerPhone,
                'vendor_id' => $vendorId,
                'order_message' => $orderMessage,
            ]);

            // Send error message to customer
            $this->whatsAppApiService->sendMessage(
                $customerPhone,
                "❌ Sorry, we encountered an error processing your order. Please try again later.",
                $vendorId
            );
        }
    }

    /**
     * Handle order flow interactions
     *
     * @param object $contact
     * @param string $messageBody
     * @param string $messageType
     * @param array $messageData
     * @param int $vendorId
     * @return bool
     */
    protected function handleOrderFlowInteractions($contact, string $messageBody, string $messageType, array $messageData, int $vendorId): bool
    {
        try {
            // Check if order processing is enabled
            if (!getVendorSettings('enable_whatsapp_orders', false, null, $vendorId)) {
                return false;
            }

            $userStateRepository = app(\App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppUserStateRepository::class);
            $userState = $userStateRepository->fetchByPhone($contact->wa_id, $vendorId);

            if (!$userState) {
                return false;
            }

            // Handle text messages for address input
            if ($messageType === 'text' && $userState->isState('awaiting_address')) {
                $orderService = app(\App\Yantrana\Components\WhatsAppService\Services\WhatsAppOrderService::class);
                $result = $orderService->handleAddressInput($contact->wa_id, $messageBody, $vendorId);

                if (!$result['success']) {
                    $this->whatsAppApiService->sendMessage(
                        $contact->wa_id,
                        "❌ Sorry, we couldn't process your address. Please try again.",
                        $vendorId
                    );
                }

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to handle order flow interaction', [
                'error' => $e->getMessage(),
                'contact_phone' => $contact->wa_id,
                'vendor_id' => $vendorId,
                'message_type' => $messageType,
            ]);

            return false;
        }
    }

    /**
     * Check if message matches trigger based on trigger type
     *
     * @param string $messageBody
     * @param string $trigger
     * @param string $triggerType
     * @return bool
     */
    private function checkTriggerMatch($messageBody, $trigger, $triggerType)
    {
        // Handle welcome trigger type
        if ($triggerType === 'welcome') {
            // Welcome triggers should always match for first-time contacts
            // This will be handled in the main bot processing logic
            return true;
        }

        // Handle new_message trigger type
        if ($triggerType === 'new_message') {
            // New message triggers should always match when there's no active flow
            // This will be handled in the main bot processing logic
            return true;
        }

        if (!$trigger) {
            return false;
        }

        // Split trigger by commas and trim each trigger word
        $triggerWords = array_map('trim', explode(',', $trigger));
        $messageBody = strtolower(trim($messageBody));

        // Check if message matches any of the trigger words based on trigger type
        foreach ($triggerWords as $triggerWord) {
            $triggerWord = strtolower(trim($triggerWord));
            $isMatch = false;

            switch ($triggerType) {
                case 'is':
                    $isMatch = $triggerWord === $messageBody;
                    break;
                case 'starts_with':
                    $isMatch = str_starts_with($messageBody, $triggerWord);
                    break;
                case 'ends_with':
                    $isMatch = str_ends_with($messageBody, $triggerWord);
                    break;
                case 'contains_word':
                    // Match whole words only
                    $isMatch = preg_match('/\b' . preg_quote($triggerWord, '/') . '\b/i', $messageBody);
                    break;
                case 'contains':
                    $isMatch = str_contains($messageBody, $triggerWord);
                    break;
                case 'stop_promotional':
                    // Handle stop promotional logic if needed
                    $isMatch = $triggerWord === $messageBody;
                    break;
                default:
                    $isMatch = $triggerWord === $messageBody;
                    break;
            }

            if ($isMatch) {
                Log::info('Trigger match found', [
                    'messageBody' => $messageBody,
                    'triggerWord' => $triggerWord,
                    'triggerType' => $triggerType,
                    'trigger' => $trigger
                ]);
                return true;
            }
        }

        return false;
    }


}