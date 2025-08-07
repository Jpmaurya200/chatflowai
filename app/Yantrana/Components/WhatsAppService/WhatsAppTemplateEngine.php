<?php
/**
* WhatsAppTemplateEngine.php - Main component file
*
* This file is part of the WhatsAppService component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\WhatsAppService;

use Illuminate\Support\Arr;
use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\WhatsAppService\Services\WhatsAppApiService;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;
use App\Yantrana\Components\WhatsAppService\Interfaces\WhatsAppTemplateEngineInterface;

class WhatsAppTemplateEngine extends BaseEngine implements WhatsAppTemplateEngineInterface
{
    /**
     * @var  WhatsAppTemplateRepository $whatsAppTemplateRepository - WhatsAppTemplate Repository
     */
    protected $whatsAppTemplateRepository;

    /**
     * @var WhatsAppApiService - WhatsApp API Service
     */
    protected $whatsAppApiService;

    /**
      * Constructor
      *
      * @param  WhatsAppTemplateRepository $whatsAppTemplateRepository - WhatsAppTemplate Repository
      * @param  WhatsAppApiService $whatsAppApiService - WhatsAppApiService
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    public function __construct(
        WhatsAppTemplateRepository $whatsAppTemplateRepository,
        WhatsAppApiService $whatsAppApiService
    ) {
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
        $this->whatsAppApiService = $whatsAppApiService;
    }

    /**
     * Templates datatable source
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareTemplatesDataTableSource()
    {
        $templatesCollection = $this->whatsAppTemplateRepository->fetchTemplatesDataTableSource();
        // required columns for DataTables
        $requireColumns = [
            '_id',
            '_uid',
            'template_name',
            'template_id',
            'language',
            'category',
            'status',
            'updated_at' => function ($templateData) {
                return formatDateTime($templateData['updated_at']);
            },
        ];

        // prepare data for the DataTables
        return $this->dataTableResponse($templatesCollection, $requireColumns);
    }

    /**
     * Prepare template update data
     *
     * @return EngineResponse
     */
    public function prepareUpdateTemplateData($whatsAppTemplateUid)
    {
        $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt($whatsAppTemplateUid);
        abortIf(__isEmpty($whatsAppTemplate), 404, __tr('Template not found'));
        $whatsAppTemplateData = Arr::get($whatsAppTemplate->toArray(), '__data.template');
        return $this->engineSuccessResponse([
            'whatsAppTemplateUid' => $whatsAppTemplateUid,
            'whatsAppTemplateData' => $whatsAppTemplateData,
        ]);
    }

    /**
     * Create Template
     *
     * @param BaseRequest $request
     * @return EngineResponse
     * @link https://developers.facebook.com/docs/whatsapp/business-management-api/message-templates
     */
    public function createOrUpdateTemplate($request)
    {
        $vendorId = getVendorId();
        $components = [];
        $carouselData = []; // Initialize carousel data
        // https://developers.facebook.com/docs/whatsapp/business-management-api/message-templates/components#media-headers
        if($request->media_header_type) {
            if ($request->media_header_type == 'text') {
                $components[] = [
                    "type" => "HEADER",
                    "format" => "TEXT",
                    "text" => $request->header_text_body,
                ];
                // example fields
                if($request->example_header_fields) {
                    $components[(count($components) - 1)]['example'] = [
                        "header_text" => [
                            $request->example_header_fields
                        ]
                    ];
                }
            } elseif(in_array($request->media_header_type, [
                'image', 'video', 'document'
            ])) {
                $components[] = [
                    "type" => "HEADER",
                    "format" => strtoupper($request->media_header_type),
                    "text" => $request->header_body,
                    'example' => [
                        'header_handle' => [
                            $this->whatsAppApiService->uploadResumableMedia($request->uploaded_media_file_name)
                          ]
                    ]
                ];
            } elseif($request->media_header_type == 'location') {
                $components[] = [
                    "type" => "HEADER",
                    "format" => strtoupper($request->media_header_type),
                ];
            } elseif($request->media_header_type == 'carousel') {
                // Handle carousel template creation - store carousel data for later
                if(!empty($request->carousel_cards) && is_array($request->carousel_cards)) {
                    foreach($request->carousel_cards as $cardData) {
                        $cardComponents = [];

                        // Add header component for each card
                        if(!empty($cardData['header_type'])) {
                            if($cardData['header_type'] == 'image' || $cardData['header_type'] == 'video') {
                                $headerComponent = [
                                    "type" => "HEADER",
                                    "format" => strtoupper($cardData['header_type'])
                                ];

                                // WhatsApp requires example field for image/video headers
                                $mediaFileName = null;

                                if(!empty($cardData['uploaded_media_file_name'])) {
                                    // Use uploaded media file
                                    $mediaFileName = $cardData['uploaded_media_file_name'];
                                } else {
                                    // Use default placeholder media file
                                    $mediaFileName = $this->getDefaultPlaceholderMedia($cardData['header_type']);
                                }

                                if($mediaFileName) {
                                    try {
                                        $mediaHandle = $this->whatsAppApiService->uploadResumableMedia($mediaFileName);
                                        if($mediaHandle) {
                                            $headerComponent['example'] = [
                                                'header_handle' => [
                                                    $mediaHandle
                                                ]
                                            ];
                                        }
                                    } catch (\Exception $e) {
                                        // Log the error but don't fail the template creation
                                        \Illuminate\Support\Facades\Log::warning('Failed to upload carousel card media: ' . $e->getMessage());
                                    }
                                }

                                $cardComponents[] = $headerComponent;
                            } elseif($cardData['header_type'] == 'product') {
                                $cardComponents[] = [
                                    "type" => "HEADER",
                                    "format" => "PRODUCT"
                                ];
                            }
                        }

                        // Add individual BODY component for each card
                        // Each carousel card MUST have its own BODY component with text
                        $cardBodyText = !empty($cardData['body_text']) ? $cardData['body_text'] : 'Card ' . (count($carouselData) + 1);
                        $cardBodyComponent = [
                            "type" => "BODY",
                            "text" => $cardBodyText
                        ];
                        
                        // Check if card body text contains variables and add example field if needed
                        $cardHasVariables = preg_match('/\{\{\d+\}\}/', $cardBodyText);
                        if($cardHasVariables) {
                            // Create example values for carousel card body variables
                            $cardExampleValues = [];
                            preg_match_all('/\{\{\d+\}\}/', $cardBodyText, $cardMatches);
                            if (!empty($cardMatches[0])) {
                                foreach ($cardMatches[0] as $match) {
                                    $cardExampleValues[] = ''; // Empty value for each variable
                                }
                            }
                            $cardBodyComponent['example'] = [
                                "body_text" => $cardExampleValues
                            ];
                        }
                        
                        $cardComponents[] = $cardBodyComponent;

                        // Add buttons for each card - carousel templates REQUIRE buttons
                        $cardButtons = [];

                        if(!empty($cardData['buttons']) && is_array($cardData['buttons'])) {
                            // Use the first button if provided
                            $firstButton = $cardData['buttons'][0] ?? null;

                            if($firstButton) {
                                if($firstButton['type'] == 'QUICK_REPLY') {
                                    $cardButtons[] = [
                                        "type" => "QUICK_REPLY",
                                        "text" => $firstButton['text'] ?? "Reply"
                                    ];
                                } elseif($firstButton['type'] == 'URL_BUTTON') {
                                    $cardButtons[] = [
                                        "type" => "URL",
                                        "text" => $firstButton['text'] ?? "Visit",
                                        "url" => $firstButton['url'] ?? "https://example.com"
                                    ];
                                } elseif($firstButton['type'] == 'SPM') {
                                    $cardButtons[] = [
                                        "type" => "SPM",
                                        "text" => $firstButton['text'] ?? "View"
                                    ];
                                }
                            }
                        }

                        // If no buttons provided, add default SPM button for product headers
                        if(empty($cardButtons) && $cardData['header_type'] == 'product') {
                            $cardButtons[] = [
                                "type" => "SPM",
                                "text" => "View"
                            ];
                        }

                        // Add buttons component if we have buttons
                        if(!empty($cardButtons)) {
                            $cardComponents[] = [
                                "type" => "BUTTONS",
                                "buttons" => $cardButtons
                            ];
                        }

                        $carouselData[] = [
                            "components" => $cardComponents
                        ];

                        // Debug logging for each card
                        \Illuminate\Support\Facades\Log::info('Added carousel card', [
                            'card_index' => count($carouselData),
                            'header_type' => $cardData['header_type'] ?? 'none',
                            'body_text' => $cardBodyText,
                            'card_has_variables' => $cardHasVariables ?? false,
                            'components_count' => count($cardComponents),
                            'components' => $cardComponents
                        ]);
                    }
                }
            }
        }
        // body text - required for all templates
        if($request->template_body || $request->media_header_type == 'carousel') {
            $bodyText = $request->template_body ?: 'Check out our products!'; // Default text for carousel
            $bodyComponent = [
                "type" => "BODY",
                "text" => $bodyText,
            ];
            // Check if body text contains variables ({{1}}, {{2}}, etc.)
            $hasVariables = preg_match('/\{\{\d+\}\}/', $bodyText);
            
            if(!empty($request->example_body_fields) and is_array($request->example_body_fields)) {
                // Convert associative array to indexed array for WhatsApp API
                $exampleValues = [];
                if (array_keys($request->example_body_fields) !== range(0, count($request->example_body_fields) - 1)) {
                    // It's an associative array, convert to indexed array
                    $exampleValues = array_values($request->example_body_fields);
                } else {
                    // It's already an indexed array
                    $exampleValues = $request->example_body_fields;
                }
                
                // Filter out null/empty values and ensure we have valid strings
                $exampleValues = array_map(function($value) {
                    return $value === null ? '' : (string)$value;
                }, $exampleValues);
                
                // Check if all example values are empty
                $allEmpty = true;
                foreach ($exampleValues as $value) {
                    if (!empty(trim($value))) {
                        $allEmpty = false;
                        break;
                    }
                }
                
                if ($allEmpty) {
                    // If all values are empty, create default example values
                    preg_match_all('/\{\{\d+\}\}/', $bodyText, $matches);
                    if (!empty($matches[0])) {
                        $exampleValues = [];
                        foreach ($matches[0] as $index => $match) {
                            $exampleValues[] = 'Sample Value ' . ($index + 1);
                        }
                    }
                }
                
                $bodyComponent['example'] = [
                    "body_text" => $exampleValues
                ];
                
                // Debug logging for example field conversion
                \Illuminate\Support\Facades\Log::info('Example field conversion', [
                    'original_example_body_fields' => $request->example_body_fields,
                    'converted_example_values' => $exampleValues,
                    'is_associative' => array_keys($request->example_body_fields) !== range(0, count($request->example_body_fields) - 1),
                    'all_empty' => $allEmpty
                ]);
            } elseif($hasVariables) {
                // If body has variables but no example fields provided, create example with empty values
                // This is required by WhatsApp API for templates with variables
                $exampleValues = [];
                preg_match_all('/\{\{\d+\}\}/', $bodyText, $matches);
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $match) {
                        $exampleValues[] = ''; // Empty value for each variable
                    }
                }
                $bodyComponent['example'] = [
                    "body_text" => $exampleValues
                ];
            }
            $components[] = $bodyComponent;

            // Debug logging
            \Illuminate\Support\Facades\Log::info('Added BODY component', [
                'body_text' => $bodyText,
                'media_header_type' => $request->media_header_type,
                'has_variables' => $hasVariables,
                'example_body_fields' => $request->example_body_fields ?? 'none',
                'component' => $bodyComponent
            ]);
        } elseif($request->media_header_type != 'carousel' && !$request->template_body) {
            // For non-carousel templates without body text, create empty body component
            // This ensures the template structure is valid
            $components[] = [
                "type" => "BODY",
                "text" => ""
            ];
        }

        // Add carousel component after body (if carousel data exists)
        if($request->media_header_type == 'carousel' && !empty($carouselData)) {
            $components[] = [
                "type" => "CAROUSEL",
                "cards" => $carouselData
            ];
        }
        if($request->template_footer) {
            $components[] = [
                "type" => "FOOTER",
                "text" => $request->template_footer
            ];
        }
        if(!empty($request->message_buttons)) {
            $buttons = [];
            $buttonIndex = 0;
            $buttonTypes = [
                'QUICK_REPLY' => 'QUICK_REPLY',
                'PHONE_NUMBER' => 'PHONE_NUMBER',
                'URL_BUTTON' => 'URL',
                'VOICE_CALL' => 'VOICE_CALL',
                'DYNAMIC_URL_BUTTON' => 'URL',
                'COPY_CODE' => 'COPY_CODE',
            ];
            foreach ($request->message_buttons as $customButtonKey => $customButton) {
                $buttons[$buttonIndex] = [
                    'type' => $buttonTypes[$customButton['type']],
                ];
                // -----
                if (in_array($customButton['type'], [
                    'QUICK_REPLY','PHONE_NUMBER', 'URL_BUTTON', 'VOICE_CALL','DYNAMIC_URL_BUTTON'
                ])) {
                    $buttons[$buttonIndex]['text'] = $customButton['text'];
                    // urls
                    if (in_array($customButton['type'], [
                        'URL_BUTTON',
                        'DYNAMIC_URL_BUTTON'
                    ])) {
                        $buttons[$buttonIndex]['url'] = $customButton['url'];
                    }
                }
                // single example
                if (in_array($customButton['type'], [
                    'COPY_CODE',
                ])) {
                    $buttons[$buttonIndex]['example'] = $customButton['example'];
                }
                if (in_array($customButton['type'], [
                    'DYNAMIC_URL_BUTTON'
                ])) {
                    $buttons[$buttonIndex]['url'] = $customButton['url'] . '{{1}}';
                    $buttons[$buttonIndex]['example'] = [
                        $customButton['example']
                    ];
                }
                // phone number
                if (in_array($customButton['type'], [
                    'PHONE_NUMBER',
                ])) {
                    $buttons[$buttonIndex]['phone_number'] = $customButton['phone_number'];
                }
                // ----
                $buttonIndex++;
            }
            if(!empty($buttons)) {
                $components[] = [
                    "type" => "BUTTONS",
                    "buttons" => $buttons
                ];
            }
        }
        // template update
        if($request->template_uid) {
            $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt($request->template_uid);
            abortIf(__isEmpty($whatsAppTemplate), null, __tr('Template not found'));
            $whatsAppTemplateData = Arr::get($whatsAppTemplate->toArray(), '__data.template');
            $createTemplateRequest = $this->whatsAppApiService->updateTemplate(
                $whatsAppTemplateData['id'],
                $whatsAppTemplateData['name'],
                $components,
                $vendorId
            );
            if($createTemplateRequest['success'] == 1) {
                return $this->engineSuccessResponse([], __tr('Your template has been updated'));
            }
            return $this->engineSuccessResponse([], __tr('Failed to update template'));
        } else  {
            // Debug logging for template creation
            \Illuminate\Support\Facades\Log::info('Creating template with components', [
                'template_name' => $request->template_name,
                'language_code' => $request->language_code,
                'category' => $request->category,
                'media_header_type' => $request->media_header_type,
                'components_count' => count($components),
                'components' => $components
            ]);

            // create new template
            $createTemplateRequest = $this->whatsAppApiService->createTemplate(
                $request->template_name,
                $request->language_code,
                $request->category,
                $components,
                $vendorId
            );
        }

        // Log the API response for debugging
        \Illuminate\Support\Facades\Log::info('Template creation API response', [
            'template_name' => $request->template_name,
            'response' => $createTemplateRequest
        ]);

        if($createTemplateRequest['status'] == 'REJECTED') {
            $this->processSyncTemplates();
            $rejectedReason = $this->whatsAppApiService->getTemplateRejectionReason($createTemplateRequest['id']);
            return $this->engineFailedResponse([], __tr('Template has been rejected due to __rejectedReason__', [
                '__rejectedReason__' => $rejectedReason['rejected_reason']
            ]));
        } elseif($createTemplateRequest['status'] == 'APPROVED') {
            $this->processSyncTemplates();
            return $this->engineSuccessResponse([], __tr('Your template has been created and approved'));
        }
        $this->processSyncTemplates();
        return $this->engineSuccessResponse([], __tr('Your template has submitted for review and it is now __templateStatus__', [
            '__templateStatus__' => $createTemplateRequest['status']
        ]));
    }

    /**
     * Sync templates with WhatsApp Cloud API
     *
     * @return EngineResponse
     */
    public function processSyncTemplates()
    {
        // fetch the whatsapp templates from api
        // @link https://developers.facebook.com/docs/graph-api/reference/whats-app-business-account/message_templates
        $whatsAppTemplates = $this->whatsAppApiService->getTemplates();
        $templatesToAdd = [];
        $vendorId = getVendorId();
        foreach ($whatsAppTemplates as $whatsAppTemplate) {
            $templatesToAdd[] = [
                'template_name' => $whatsAppTemplate['name'],
                'language' => $whatsAppTemplate['language'],
                'template_id' => $whatsAppTemplate['id'],
                'category' => $whatsAppTemplate['category'],
                'status' => $whatsAppTemplate['status'],
                'language' => $whatsAppTemplate['language'],
                '__data' => [
                    'template' => $whatsAppTemplate,
                ],
                'vendors__id' => $vendorId,
            ];
        }
        if ($this->whatsAppTemplateRepository->syncTemplates($templatesToAdd)) {
            return $this->engineSuccessResponse(['reloadDatatableId' => '#lwTemplatesList'], __tr('Templates Sync successfully'));
        }
        return $this->engineResponse(14, [], __tr('Nothing Updated'));
    }

    /**
     * Delete the requested template
     *
     * @param  string|int  $whatsappTemplateUid
     * @return EngineResponse
     */
    public function processDeleteTemplate($whatsappTemplateUid)
    {
        $whatsAppTemplate = $this->whatsAppTemplateRepository->fetchIt($whatsappTemplateUid);
        abortIf(__isEmpty($whatsAppTemplate), null, __tr('Template not found in the system'));
        $deleteTemplate = $this->whatsAppApiService->deleteTemplate($whatsAppTemplate->template_name, $whatsAppTemplate->template_id);
        if (isset($deleteTemplate['success']) and $deleteTemplate['success']) {
            $this->processSyncTemplates();
            return $this->engineSuccessResponse(['reloadDatatableId' => '#lwTemplatesList'], __tr('Template deleted successfully.'));
        }

        return $this->engineFailedResponse([], __tr('Failed to delete template'));
    }

    /**
     * Get default placeholder media file for carousel cards
     *
     * @param string $mediaType
     * @return string|null
     */
    private function getDefaultPlaceholderMedia($mediaType)
    {
        // Use existing image as placeholder for both image and video
        // WhatsApp just needs a valid media file for the example
        $fileName = 'carousel_placeholder_' . $mediaType . '.png';
        $tempPath = getPathByKey('user_temp_uploads', ['{_uid}' => authUID()]);
        $filePath = $tempPath . DIRECTORY_SEPARATOR . $fileName;

        // Check if placeholder file already exists
        if (file_exists($filePath)) {
            return $fileName;
        }

        // Copy existing image as placeholder
        try {
            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0777, true);
            }

            // Use existing WhatsApp background image as placeholder
            $sourceImagePath = public_path('imgs/wa-message-bg.png');
            if (file_exists($sourceImagePath)) {
                copy($sourceImagePath, $filePath);
                return $fileName;
            }

            // Fallback: create simple placeholder if source image doesn't exist
            $this->createSimplePlaceholder($filePath, $mediaType);
            return file_exists($filePath) ? $fileName : null;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create placeholder media: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a simple placeholder image for carousel cards
     *
     * @param string $filePath
     * @param string $mediaType
     */
    private function createSimplePlaceholder($filePath, $mediaType)
    {
        // Create a simple 400x300 placeholder image
        $width = 400;
        $height = 300;
        $image = imagecreate($width, $height);

        // Set colors based on media type
        if ($mediaType === 'video') {
            imagecolorallocate($image, 200, 200, 255); // Light blue for video
            $textColor = imagecolorallocate($image, 50, 50, 100);
            $text = 'Carousel Video';
        } else {
            imagecolorallocate($image, 240, 240, 240); // Light gray for image
            $textColor = imagecolorallocate($image, 100, 100, 100);
            $text = 'Carousel Image';
        }

        // Add text
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        imagestring($image, $fontSize, $x, $y, $text, $textColor);

        // Save as PNG to match the filename
        imagepng($image, $filePath);
        imagedestroy($image);
    }
}