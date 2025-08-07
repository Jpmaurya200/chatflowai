<?php
/**
* WhatsAppTemplateController.php - Controller file
*
* This file is part of the WhatsAppService component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Base\BaseRequestTwo;
use App\Yantrana\Components\WhatsAppService\WhatsAppTemplateEngine;
use Illuminate\Validation\Rule;

class WhatsAppTemplateController extends BaseController
{       /**
     * @var  WhatsAppTemplateEngine $whatsAppTemplateEngine - WhatsAppTemplate Engine
     */
    protected $whatsAppTemplateEngine;

    /**
      * Constructor
      *
      * @param  WhatsAppTemplateEngine $whatsAppTemplateEngine - WhatsAppTemplate Engine
      *
      * @return  void
      *-----------------------------------------------------------------------*/
    public function __construct(WhatsAppTemplateEngine $whatsAppTemplateEngine)
    {
        $this->whatsAppTemplateEngine = $whatsAppTemplateEngine;
    }

    /**
     * list of Templates
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function showTemplatesView()
    {
        validateVendorAccess('messaging');
        // load the view
        return $this->loadView('whatsapp-service.templates-list');
    }

    /**
     * list of Templates
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function prepareTemplatesList()
    {
        validateVendorAccess('manage_templates');
        // respond with dataTables preparations
        return $this->whatsAppTemplateEngine->prepareTemplatesDataTableSource();
    }

    /**
     * Create new template view
     *
     * @return view
     */
    public function createNewTemplate()
    {
        validateVendorAccess('manage_templates');
        // load the view
        return $this->loadView('whatsapp-service.templates.new-template');
    }

    /**
     * New Template creation process
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function createNewTemplateProcess(BaseRequestTwo $request)
    {
        validateVendorAccess('manage_templates');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $validations = [
            'template_name' => [
                'required',
                'max:512',
                'alpha_dash',
            ],
            'language_code' => [
                'required',
                'max:15',
                'alpha_dash',
            ],
            'template_body' => [
                'required', // Always required, including for carousel
                'max:1024',
            ],
            'template_footer' => [
                'max:60',
                'nullable',
            ],
            'category' => [
                'required',
                Rule::in([
                    'MARKETING',
                    'UTILITY',
                    'AUTHENTICATION',
                ]),
            ],
        ];

        // Add validation for example fields when template body contains variables
        if (!empty($request->template_body) && preg_match('/\{\{\d+\}\}/', $request->template_body)) {
            $validations['example_body_fields'] = [
                'required',
                'array'
            ];
            
            // Add validation for each example field
            preg_match_all('/\{\{\d+\}\}/', $request->template_body, $matches);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $match) {
                    $variableNumber = preg_replace('/\{\{(\d+)\}\}/', '$1', $match);
                    $validations["example_body_fields.{$variableNumber}"] = [
                        'required',
                        'string',
                        'min:1',
                        'max:100'
                    ];
                }
            }
        }

        // Add carousel-specific validations
        if($request->media_header_type === 'carousel') {
            // Debug: Log carousel cards data
            \Illuminate\Support\Facades\Log::info('Carousel template validation - carousel_cards data:', [
                'carousel_cards' => $request->carousel_cards,
                'media_header_type' => $request->media_header_type
            ]);
            $validations['carousel_cards'] = [
                'required',
                'array',
                'min:2',
                'max:10'
            ];
            $validations['carousel_cards.*.header_type'] = [
                'required',
                Rule::in(['image', 'video', 'product'])
            ];
            $validations['carousel_cards.*.body_text'] = [
                'nullable',
                'max:160'
            ];
            $validations['carousel_cards.*.buttons'] = [
                'nullable',
                'array',
                'max:1' // Carousel cards support only 1 button each
            ];
            $validations['carousel_cards.*.buttons.*.type'] = [
                'required_with:carousel_cards.*.buttons',
                Rule::in(['QUICK_REPLY', 'URL_BUTTON', 'SPM'])
            ];
            $validations['carousel_cards.*.buttons.*.text'] = [
                'required_with:carousel_cards.*.buttons',
                'max:25'
            ];
            // Add URL validation only for URL_BUTTON type buttons
            if(!empty($request->carousel_cards)) {
                foreach($request->carousel_cards as $cardIndex => $cardData) {
                    if(!empty($cardData['buttons'])) {
                        foreach($cardData['buttons'] as $buttonIndex => $buttonData) {
                            if(($buttonData['type'] ?? '') === 'URL_BUTTON') {
                                $validations["carousel_cards.{$cardIndex}.buttons.{$buttonIndex}.url"] = [
                                    'required',
                                    'url',
                                    'max:2000'
                                ];
                            }
                        }
                    }
                }
            }
            // Temporarily disable media file validation to test file upload functionality
            // TODO: Re-enable once file upload integration is confirmed working
            /*
            // Add conditional validation for media files in carousel cards
            if(!empty($request->carousel_cards)) {
                foreach($request->carousel_cards as $cardIndex => $cardData) {
                    $headerType = $cardData['header_type'] ?? '';

                    // Only validate uploaded_media_file_name for image and video header types
                    if($headerType === 'image') {
                        $validations["carousel_cards.{$cardIndex}.uploaded_media_file_name"] = [
                            'required_if:carousel_cards.' . $cardIndex . '.header_type,image',
                            'nullable',
                            'string'
                        ];
                    } elseif($headerType === 'video') {
                        $validations["carousel_cards.{$cardIndex}.uploaded_media_file_name"] = [
                            'required_if:carousel_cards.' . $cardIndex . '.header_type,video',
                            'nullable',
                            'string'
                        ];
                    }
                    // Product header type doesn't need uploaded_media_file_name validation
                }
            }
            */
            // if(!empty($request->carousel_cards)) {
            //     foreach($request->carousel_cards as $cardIndex => $cardData) {
            //         if(in_array($cardData['header_type'] ?? '', ['image', 'video'])) {
            //             $validations["carousel_cards.{$cardIndex}.uploaded_media_file_name"] = [
            //                 'required',
            //                 'string'
            //             ];
            //         }
            //     }
            // }
        }
        if($request->media_header_type) {
            if(!in_array($request->media_header_type, [
                'text',
                'location',
                'carousel', // Exclude carousel from requiring main uploaded_media_file_name
            ])) {
                $validations["uploaded_media_file_name"] = [
                    'required',
                ];
            } elseif($request->media_header_type == 'text') {
                $validations["header_text_body"] = [
                    'required',
                    'max:60',
                ];
            }
        }
        // custom buttons
        if(!empty($request->message_buttons)) {
            foreach ($request->message_buttons as $customButtonKey => $customButton) {
                // button texts
                if (in_array($customButton['type'], [
                    'QUICK_REPLY','PHONE_NUMBER', 'URL_BUTTON', 'VOICE_CALL','DYNAMIC_URL_BUTTON'
                ])) {
                    $validations["message_buttons.$customButtonKey.text"] = [
                        'required',
                        'max:25',
                    ];
                    // urls
                    if (in_array($customButton['type'], [
                        'URL_BUTTON',
                        'DYNAMIC_URL_BUTTON'
                    ])) {
                        $validations["message_buttons.$customButtonKey.url"] = [
                            'required',
                            'max:2000',
                            'url'
                        ];
                    }
                }

                // single example
                if (in_array($customButton['type'], [
                    'COPY_CODE',
                    'DYNAMIC_URL_BUTTON'
                ])) {
                    $validations["message_buttons.$customButtonKey.example"] = [
                        'required',
                        'alpha_dash'
                    ];
                }
                // phone number
                if (in_array($customButton['type'], [
                    'PHONE_NUMBER',
                ])) {
                    $validations["message_buttons.$customButtonKey.phone_number"] = [
                        'required',
                        'numeric'
                    ];
                }
            }
        }

        $request->validate($validations);
        $processResponse = $this->whatsAppTemplateEngine->createOrUpdateTemplate($request);
        if($processResponse->success()) {
            return $this->processResponse(21, [], [
                'redirectUrl' => route('vendor.whatsapp_service.templates.read.list_view'),
                'show_message' => true,
                'messageType' => 'success',
            ], true);
        }
        return $this->processResponse($processResponse);
    }

    /**
     * Create new template view
     *
     * @return view
     */
    public function updateTemplate($templateUid)
    {
        validateVendorAccess('manage_templates');
        $whatsAppTemplateDate = $this->whatsAppTemplateEngine->prepareUpdateTemplateData($templateUid);
        // load the view
        return $this->loadView('whatsapp-service.templates.update-template', $whatsAppTemplateDate->data(), [
            'compress_page' => false
        ]);
    }

    /**
     * New Template creation process
     *
     * @param BaseRequestTwo $request
     * @return json
     */
    public function updateTemplateProcess(BaseRequestTwo $request)
    {
        validateVendorAccess('manage_templates');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        $validations = [
            'template_body' => [
                'required_unless:media_header_type,carousel',
                'max:1024',
            ],
            'template_uid' => [
                'required',
            ],
            'template_footer' => [
                'max:60',
                'nullable',
            ],
        /*     'category' => [
                'required',
                Rule::in([
                    'MARKETING',
                    'UTILITY',
                    'AUTHENTICATION',
                ]),
            ], */
        ];

        // Add carousel-specific validations for update
        if($request->media_header_type === 'carousel') {
            $validations['carousel_cards'] = [
                'required',
                'array',
                'min:2',
                'max:10'
            ];
            $validations['carousel_cards.*.header_type'] = [
                'required',
                Rule::in(['image', 'video', 'product'])
            ];
            $validations['carousel_cards.*.body_text'] = [
                'nullable',
                'max:160'
            ];
            $validations['carousel_cards.*.buttons'] = [
                'nullable',
                'array',
                'max:2'
            ];
            $validations['carousel_cards.*.buttons.*.type'] = [
                'required_with:carousel_cards.*.buttons',
                Rule::in(['QUICK_REPLY', 'URL_BUTTON', 'SPM'])
            ];
            $validations['carousel_cards.*.buttons.*.text'] = [
                'required_with:carousel_cards.*.buttons',
                'max:25'
            ];
            $validations['carousel_cards.*.buttons.*.url'] = [
                'required_if:carousel_cards.*.buttons.*.type,URL_BUTTON',
                'url',
                'max:2000'
            ];
            // Temporarily disable media file validation for update to test file upload functionality
            // TODO: Re-enable once file upload integration is confirmed working
            /*
            // Add conditional validation for media files in carousel cards for update
            if(!empty($request->carousel_cards)) {
                foreach($request->carousel_cards as $cardIndex => $cardData) {
                    $headerType = $cardData['header_type'] ?? '';

                    // Only validate uploaded_media_file_name for image and video header types
                    if($headerType === 'image') {
                        $validations["carousel_cards.{$cardIndex}.uploaded_media_file_name"] = [
                            'required_if:carousel_cards.' . $cardIndex . '.header_type,image',
                            'nullable',
                            'string'
                        ];
                    } elseif($headerType === 'video') {
                        $validations["carousel_cards.{$cardIndex}.uploaded_media_file_name"] = [
                            'required_if:carousel_cards.' . $cardIndex . '.header_type,video',
                            'nullable',
                            'string'
                        ];
                    }
                    // Product header type doesn't need uploaded_media_file_name validation
                }
            }
            */
            //             $validations["carousel_cards.{$cardIndex}.uploaded_media_file_name"] = [
            //                 'required',
            //                 'string'
            //             ];
            //         }
            //     }
            // }
        }
        if($request->media_header_type) {
            if(!in_array($request->media_header_type, [
                'text',
                'location',
                'carousel', // Exclude carousel from requiring main uploaded_media_file_name
            ])) {
                $validations["uploaded_media_file_name"] = [
                    'required',
                ];
            } elseif($request->media_header_type == 'text') {
                $validations["header_text_body"] = [
                    'required',
                    'max:60',
                ];
            }
        }
        // custom buttons
        if(!empty($request->message_buttons)) {
            foreach ($request->message_buttons as $customButtonKey => $customButton) {
                // button texts
                if (in_array($customButton['type'], [
                    'QUICK_REPLY','PHONE_NUMBER', 'URL_BUTTON', 'VOICE_CALL','DYNAMIC_URL_BUTTON'
                ])) {
                    $validations["message_buttons.$customButtonKey.text"] = [
                        'required',
                        'max:25',
                    ];
                    // urls
                    if (in_array($customButton['type'], [
                        'URL_BUTTON',
                        'DYNAMIC_URL_BUTTON'
                    ])) {
                        $validations["message_buttons.$customButtonKey.url"] = [
                            'required',
                            'max:2000',
                            'url'
                        ];
                    }
                }

                // single example
                if (in_array($customButton['type'], [
                    'COPY_CODE',
                    'DYNAMIC_URL_BUTTON'
                ])) {
                    $validations["message_buttons.$customButtonKey.example"] = [
                        'required',
                        'alpha_dash'
                    ];
                }
                // phone number
                if (in_array($customButton['type'], [
                    'PHONE_NUMBER',
                ])) {
                    $validations["message_buttons.$customButtonKey.phone_number"] = [
                        'required',
                        'numeric'
                    ];
                }
            }
        }
        $request->validate($validations);
        $processResponse = $this->whatsAppTemplateEngine->createOrUpdateTemplate($request);
        return $this->processResponse($processResponse);
    }

    /**
     * Sync templates with Meta account
     *
     * @return json
     */
    public function syncTemplates()
    {
        validateVendorAccess([
            'messaging',
            'manage_templates'
        ]);
        if(!isWhatsAppBusinessAccountReady()) {
            return $this->processResponse(22, [
                22 => __tr('Please complete your WhatsApp Cloud API Setup first')
            ], [], true);
        }
        return $this->processResponse(
            $this->whatsAppTemplateEngine->processSyncTemplates(),
            [],
            [],
            true
        );
    }

    /**
     * Ask to delete the template
     *
     * @param BaseRequestTwo $request
     * @param mixed $whatsappTemplateId
     * @return json
     */
    public function deleteTemplate(BaseRequestTwo $request, $whatsappTemplateId)
    {
        validateVendorAccess('manage_templates');
        // restrict demo user
        if(isDemo() and isDemoVendorAccount()) {
            return $this->processResponse(22, [
                22 => __tr('Functionality is disabled in this demo.')
            ], [], true);
        }
        // ask engine to process the request
        $processReaction = $this->whatsAppTemplateEngine->processDeleteTemplate($whatsappTemplateId);

        // get back with response
        return $this->processResponse($processReaction, [], [], true);
    }
}