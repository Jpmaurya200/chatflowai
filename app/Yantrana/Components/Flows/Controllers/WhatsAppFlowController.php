<?php

namespace App\Yantrana\Components\Flows\Controllers;

use App\Yantrana\Base\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Yantrana\Components\Vendor\VendorSettingsEngine;
use App\Yantrana\Components\Vendor\Models\VendorSettingsModel;
use Illuminate\Support\Arr;
use App\Yantrana\Components\Flows\Services\WhatsAppFlowService;

class WhatsAppFlowController extends BaseController 
{
    protected $baseUrl;
    protected $wabaId;
    protected $accessToken;
    protected $phoneNumberId;
    protected $defaultFlowJson;
    protected $vendorSettingsEngine;

    public function __construct(
        VendorSettingsEngine $vendorSettingsEngine
    )
    {
        $this->vendorSettingsEngine = $vendorSettingsEngine;
        $this->baseUrl = "https://graph.facebook.com/v20.0";
        
        // Fetching values using getVendorSettings helper function
        $this->wabaId = getVendorSettings('whatsapp_business_account_id');
        $this->accessToken = getVendorSettings('whatsapp_access_token');
        $this->phoneNumberId = getVendorSettings('current_phone_number_id');

        // Log the settings we're trying to use for debugging
        Log::info('WhatsApp Flow Controller Settings', [
            'wabaId' => $this->wabaId,
            'phoneNumberId' => $this->phoneNumberId,
            'accessToken' => $this->accessToken ? 'Set' : 'Not Set'
        ]);

        $this->defaultFlowJson = [
            "version" => "5.0",
            "screens" => [
                [
                    "id" => "WELCOME_SCREEN",
                    "layout" => [
                        "type" => "SingleColumnLayout",
                        "children" => [
                            [
                                "type" => "TextHeading",
                                "text" => "Hello World"
                            ],
                            [
                                "type" => "Footer",
                                "label" => "Complete",
                                "on-click-action" => [
                                    "name" => "complete",
                                    "payload" => []
                                ]
                            ]
                        ]
                    ],
                    "title" => "Welcome",
                    "terminal" => true,
                    "success" => true,
                    "data" => []
                ]
            ]
        ];
    }

    /**
     * Check if WhatsApp configuration is valid
     *
     * @return bool
     */
    protected function isWhatsAppConfigValid()
    {
        if (empty($this->wabaId) || empty($this->accessToken) || empty($this->phoneNumberId)) {
            Log::error('Invalid WhatsApp configuration', [
                'wabaId' => $this->wabaId,
                'phoneNumberId' => $this->phoneNumberId,
                'accessToken' => !empty($this->accessToken)
            ]);
            
            return false;
        }
        
        return true;
    }

    /**
     * Setup and call WhatsApp API directly
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @return array|null
     */
    protected function callWhatsAppApi($endpoint, $params = [], $method = 'GET')
    {
        try {
            // Initialize with vendorId
            $vendorId = getVendorId();
            
            // Get the business account ID and token directly
            $wabaId = getVendorSettings('whatsapp_business_account_id', null, null, $vendorId);
            $accessToken = getVendorSettings('whatsapp_access_token', null, null, $vendorId);
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token', [
                    'vendorId' => $vendorId
                ]);
                return null;
            }
            
            // Base URL for API requests - Default to v17.0 which is more stable
            $baseUrl = "https://graph.facebook.com/v17.0/";
            
            // Build the full URL based on the endpoint format
            $fullUrl = $baseUrl;
            
            // If the endpoint is a full flow ID (not a path with wabaId/flows/id)
            if (is_numeric($endpoint) || (strlen($endpoint) > 10 && !str_contains($endpoint, '/'))) {
                // Directly use the flow ID
                $fullUrl .= $endpoint;
                Log::info('Using direct flow ID format');
            } else if (strpos($endpoint, $wabaId) === 0) {
                // If endpoint already starts with wabaId, just use it directly
                $fullUrl .= $endpoint;
            } else if (strpos($endpoint, '/') === 0) {
                // If endpoint starts with /, trim it
                $fullUrl .= ltrim($endpoint, '/');
            } else {
                // Otherwise, normal endpoint
                $fullUrl .= $endpoint;
            }
            
            // Log the attempt with all details
            Log::info('Calling WhatsApp API', [
                'vendorId' => $vendorId,
                'wabaId' => $wabaId,
                'endpoint' => $endpoint,
                'fullUrl' => $fullUrl, 
                'method' => $method,
                'params' => $params,
                'has_token' => !empty($accessToken)
            ]);
            
            // Make the actual request using Http facade
            $response = null;
            if ($method === 'GET') {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->get($fullUrl, $params);
            } else if ($method === 'POST') {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json'
                ])->post($fullUrl, $params);
            } else if ($method === 'DELETE') {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])->delete($fullUrl, $params);
            }
            
            if ($response && $response->successful()) {
                Log::info('WhatsApp API call successful', [
                    'status' => $response->status()
                ]);
                return $response->json();
            } else if ($response) {
                Log::error('WhatsApp API call failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'url' => $fullUrl
                ]);
                return null;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp API call failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Display a listing of WhatsApp flows.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $cursor = $request->query('cursor');
            $wabaId = getVendorSettings('whatsapp_business_account_id');
            
            // Use the wrapper method to call the API - For listing, we need the wabaId prefix
            $response = $this->callWhatsAppApi("{$wabaId}/flows", [
                'after' => $cursor
            ]);
            
            if (!empty($response) && isset($response['data'])) {
                $flows = $response['data'] ?? [];
                $pagination = null;
                
                if (isset($response['paging']['cursors'])) {
                    $pagination = [
                        'before' => $response['paging']['cursors']['before'] ?? null,
                        'after' => $response['paging']['cursors']['after'] ?? null,
                    ];
                }
                
                return view('whatsapp-flows.index', [
                    'flows' => $flows,
                    'pagination' => $pagination,
                ]);
            }
            
            // If we get here, the API call failed
            Log::error('Failed to get WhatsApp flows', [
                'response' => $response
            ]);
            
            return view('whatsapp-flows.index', [
                'flows' => [],
                'error' => 'Failed to fetch WhatsApp flows. Please check your WhatsApp API configuration.'
            ])->with('error', 'Failed to fetch WhatsApp flows.');
        } catch (\Exception $e) {
            Log::error('WhatsApp Flows Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('whatsapp-flows.index', [
                'flows' => [],
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show the details of a specific flow.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            // Get the access token
            $accessToken = getVendorSettings('whatsapp_access_token');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }
            
            // Call the API using the ID directly
            $response = $this->callWhatsAppApi($id);

            if (!empty($response)) {
                return view('whatsapp-flows.show', ['flow' => $response]);
            } else {
                return redirect()->route('whatsapp-flows.index')
                    ->with('error', 'Failed to fetch flow details.');
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Details Exception', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return redirect()->route('whatsapp-flows.index')
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Refresh flows data from the API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $wabaId = getVendorSettings('whatsapp_business_account_id');
            
            // For listing flows, we need the wabaId prefix
            $response = $this->callWhatsAppApi("{$wabaId}/flows");

            if (!empty($response)) {
                app(WhatsAppFlowService::class)->clearCachedFlows();
                return response()->json(['success' => true]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'API request failed'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Flows Refresh Exception', [
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show the edit form for a specific flow.
     *
     * @param  string  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            Log::info('Attempting to fetch flow details for editing', [
                'flow_id' => $id
            ]);

            // Get the access token
            $accessToken = getVendorSettings('whatsapp_access_token');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }

            // Call the API using the ID directly as per sample
            $response = $this->callWhatsAppApi($id, [
                'fields' => 'id,name,categories,preview,status,validation_errors,json_version,data_api_version,endpoint_uri,whatsapp_business_account,application,health_status'
            ]);

            Log::info('API Response for edit', [
                'response' => $response ? 'Success' : 'Failed',
                'status' => $response['status'] ?? null
            ]);

            if (!empty($response)) {
                return view('whatsapp-flows.edit', [
                    'flow' => $response
                ]);
            }

            // If response wasn't successful, log error
            Log::error('WhatsApp API Error when editing', [
                'flow_id' => $id
            ]);
            
            throw new Exception("API Error: Unable to fetch flow details");

        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Edit Exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('whatsapp-flows.index')
                ->with('error', 'Failed to fetch flow details for editing: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'categories' => 'required|array|min:1',
                'categories.*' => 'required|string|in:SIGN_UP,SIGN_IN,APPOINTMENT_BOOKING,LEAD_GENERATION,CONTACT_US,CUSTOMER_SUPPORT,SURVEY,OTHER'
            ]);

            Log::info('Updating WhatsApp flow', [
                'flow_id' => $id,
                'request_data' => $validated
            ]);

            // Get the access token
            $accessToken = getVendorSettings('whatsapp_access_token');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }

            // Make the API request to update the flow - use ID directly
            $response = $this->callWhatsAppApi($id, [
                'name' => $validated['name'],
                'categories' => $validated['categories']
            ], 'POST');

            Log::info('Update API Response', [
                'response' => $response
            ]);

            if (!empty($response)) {
                return redirect()->route('whatsapp-flows.index')
                    ->with('success', 'Flow updated successfully!');
            }

            // If response wasn't successful, log error
            Log::error('WhatsApp API Update Error', [
                'id' => $id
            ]);
            
            throw new Exception("API Error: Failed to update flow");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Update Exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update flow: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for creating a new WhatsApp flow.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('whatsapp-flows.create', [
            'defaultFlowJson' => json_encode($this->defaultFlowJson, JSON_PRETTY_PRINT)
        ]);
    }

    /**
     * Create a new WhatsApp flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createFlow(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'categories' => 'required|array|min:1',
                'categories.*' => 'required|string|in:SIGN_UP,SIGN_IN,APPOINTMENT_BOOKING,LEAD_GENERATION,CONTACT_US,CUSTOMER_SUPPORT,SURVEY,OTHER',
                'flow_json' => 'required|string',
                'publish' => 'nullable|boolean'
            ]);

            // Parse flow_json to ensure it's valid JSON
            $flowJsonData = json_decode($request->flow_json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()
                    ->with('error', 'Invalid JSON format: ' . json_last_error_msg())
                    ->withInput();
            }

            // Validate flow JSON structure
            if (!isset($flowJsonData->version) || !isset($flowJsonData->screens)) {
                return redirect()->back()
                    ->with('error', 'Invalid flow structure: version and screens are required')
                    ->withInput();
            }

            // Get the access token and wabaId
            $accessToken = getVendorSettings('whatsapp_access_token');
            $wabaId = getVendorSettings('whatsapp_business_account_id');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }
            
            if (empty($wabaId)) {
                Log::error('Missing WhatsApp Business Account ID');
                throw new Exception("WhatsApp Business Account ID not configured");
            }

            // Prepare request data
            $requestData = [
                'name' => $validated['name'],
                'categories' => $validated['categories'],
                'flow_json' => $request->flow_json,
                'publish' => $request->boolean('publish', false)
            ];

            // Log the request data for debugging
            Log::info('Creating WhatsApp flow', [
                'requestData' => $requestData
            ]);

            // Make API request using our wrapper - for flow creation, we need the wabaId prefix
            $response = $this->callWhatsAppApi("{$wabaId}/flows", $requestData, 'POST');

            // Log the response for debugging
            Log::info('WhatsApp API response for flow creation', [
                'response' => $response
            ]);

            if (!empty($response)) {
                // Check if there are validation errors in the response
                if (isset($response['validation_errors']) && !empty($response['validation_errors'])) {
                    $errorMessages = [];
                    foreach ($response['validation_errors'] as $error) {
                        $errorMessages[] = $error['message'] ?? 'Validation error';
                    }
                    
                    return redirect()->back()
                        ->with('error', 'Flow created with validation warnings: ' . implode(', ', $errorMessages))
                        ->withInput();
                }
                
                // Redirect to the index page with a success message
                return redirect()->route('whatsapp-flows.index')
                    ->with('success', 'Flow created successfully!');
            }

            // Handle error response
            return redirect()->back()
                ->with('error', 'Failed to create flow')
                ->withInput();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while creating the flow: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a WhatsApp flow.
     *
     * @param  string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        try {
            Log::info('Attempting to delete WhatsApp flow', ['id' => $id]);
            
            // Get the access token
            $accessToken = getVendorSettings('whatsapp_access_token');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }
            
            // Try to get the flow first to check its status - use ID directly
            $flow = $this->callWhatsAppApi($id);
            
            $statusCheckSkipped = false;
            
            if (!empty($flow)) {
                Log::info('Flow details retrieved', ['flow' => $flow]);
                
                // Check if flow is in DRAFT status
                if ($flow['status'] !== 'DRAFT') {
                    Log::warning('Attempted to delete non-draft flow', [
                        'id' => $id,
                        'status' => $flow['status']
                    ]);
                    return redirect()->route('whatsapp-flows.index')
                        ->with('error', 'Only draft flows can be deleted');
                }
            } else {
                // If we can't get the flow details, log the error but continue with deletion
                Log::warning('Failed to get flow details, proceeding with deletion anyway', [
                    'id' => $id
                ]);
                $statusCheckSkipped = true;
            }

            // Delete the flow using the correct endpoint format - use ID directly
            Log::info('Sending delete request to WhatsApp API', [
                'id' => $id
            ]);
            
            $deleteResponse = $this->callWhatsAppApi($id, [], 'DELETE');

            Log::info('Delete response received', [
                'response' => $deleteResponse
            ]);

            if (!empty($deleteResponse) && ($deleteResponse['success'] ?? false)) {
                return redirect()->route('whatsapp-flows.index')
                    ->with('success', 'Flow deleted successfully');
            }

            return redirect()->route('whatsapp-flows.index')
                ->with('error', 'Failed to delete flow');

        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Delete Exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('whatsapp-flows.index')
                ->with('error', 'An error occurred while deleting the flow: ' . $e->getMessage());
        }
    }

    /**
     * Show the preview page for a flow.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function preview($id)
    {
        try {
            Log::info('Attempting to fetch flow preview', [
                'flow_id' => $id
            ]);

            // Get the access token
            $accessToken = getVendorSettings('whatsapp_access_token');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }

            // Call the API using the ID directly
            $response = $this->callWhatsAppApi($id, [
                'fields' => 'preview.invalidate(false)'
            ]);

            if (empty($response)) {
                throw new Exception('Failed to fetch preview');
            }

            if (!isset($response['preview']) || !isset($response['preview']['preview_url'])) {
                throw new Exception('Preview URL not available');
            }

            return view('whatsapp-flows.preview', [
                'flow' => [
                    'id' => $id,
                    'preview_url' => $response['preview']['preview_url'],
                    'expires_at' => $response['preview']['expires_at'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Preview Exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('whatsapp-flows.index')
                ->with('error', 'Failed to load preview: ' . $e->getMessage());
        }
    }

    /**
     * Show the send flow form
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function showSendForm($id)
    {
        try {
            // Log the request details for debugging
            Log::info('Fetching flow details for send form', [
                'flow_id' => $id
            ]);
            
            // Get the access token
            $accessToken = getVendorSettings('whatsapp_access_token');
            
            if (empty($accessToken)) {
                Log::error('Missing WhatsApp access token');
                throw new Exception("WhatsApp access token not configured");
            }
            
            // Use expanded debug logging
            Log::info('Send form API request details', [
                'id' => $id
            ]);

            // Call the API using the ID directly as per sample
            $response = $this->callWhatsAppApi($id, [
                'fields' => 'id,name,categories,preview,status,validation_errors,json_version,data_api_version,endpoint_uri,whatsapp_business_account,application,health_status'
            ]);

            // Log the response for debugging
            Log::info('Flow details response for send form', [
                'response' => $response ? 'Success' : 'Failed',
                'has_data' => !empty($response),
                'status' => $response['status'] ?? null
            ]);

            if (!empty($response)) {
                $flow = $response;
                
                // Check if flow can be sent
                $canSendMessage = $flow['health_status']['can_send_message'] ?? 'BLOCKED';
                $errors = [];
                
                if (isset($flow['health_status']['entities'])) {
                    foreach ($flow['health_status']['entities'] as $entity) {
                        if ($entity['entity_type'] === 'FLOW' && isset($entity['errors'])) {
                            $errors = array_merge($errors, array_map(function($error) {
                                return $error['error_description'];
                            }, $entity['errors']));
                        }
                    }
                }
                
                return view('whatsapp-flows.send', [
                    'flow' => $flow,
                    'canSendMessage' => $canSendMessage,
                    'errors' => $errors
                ]);
            } else {
                Log::error('WhatsApp API Error when loading send form', [
                    'flow_id' => $id
                ]);
                
                throw new Exception("API Error: Failed to fetch flow details");
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Send Form Exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('whatsapp-flows.index')
                ->with('error', 'Failed to load send form: ' . $e->getMessage());
        }
    }

    /**
     * Get preview token for a flow by ID.
     *
     * @param string $flowId
     * @param string $accessToken
     * @return string|null
     */
    protected function getFlowPreviewToken($flowId, $accessToken)
    {
        try {
            $baseUrl = "https://graph.facebook.com/v17.0";
            
            // Make a direct call to invalidate the preview and get a fresh token
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get("{$baseUrl}/{$flowId}", [
                'fields' => 'preview.invalidate(true)'
            ]);
            
            Log::info('Getting preview token', [
                'flow_id' => $flowId,
                'response_status' => $response->status()
            ]);
            
            if (!$response->successful()) {
                Log::error('Preview token API call failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            }
            
            $data = $response->json();
            
            // Check if preview data exists
            if (!isset($data['preview']) || !isset($data['preview']['preview_url'])) {
                Log::error('Preview data missing in response', [
                    'response' => $data
                ]);
                return null;
            }
            
            $previewUrl = $data['preview']['preview_url'];
            Log::info('Preview URL received', ['url' => $previewUrl]);
            
            // Try different methods to extract token
            $token = null;
            
            // Method 1: Standard query parameter format
            if (preg_match('/[\?&]preview_token=([^&]+)/', $previewUrl, $matches)) {
                $token = $matches[1];
            }
            // Method 2: Alternative parameter name
            else if (preg_match('/[\?&]token=([^&]+)/', $previewUrl, $matches)) {
                $token = $matches[1];
            }
            // Method 3: Parse URL query parameters
            else {
                $parsedUrl = parse_url($previewUrl);
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                    if (isset($queryParams['preview_token'])) {
                        $token = $queryParams['preview_token'];
                    } else if (isset($queryParams['token'])) {
                        $token = $queryParams['token'];
                    }
                }
            }
            
            if ($token) {
                Log::info('Preview token found', ['token' => $token]);
                return $token;
            }
            
            Log::error('Could not extract token from preview URL', [
                'url' => $previewUrl
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting preview token', [
                'flow_id' => $flowId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Send flow to a customer.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request, $id)
    {
        try {
            Log::info('Sending WhatsApp flow', [
                'flow_id' => $id,
                'request_data' => $request->all()
            ]);

            // Get required settings
            $accessToken = getVendorSettings('whatsapp_access_token');
            $phoneNumberId = getVendorSettings('current_phone_number_id');
            $baseUrl = "https://graph.facebook.com/v17.0";

            if (empty($accessToken) || empty($phoneNumberId)) {
                throw new Exception('Missing WhatsApp configuration: ' . 
                    (empty($accessToken) ? 'Access Token ' : '') . 
                    (empty($phoneNumberId) ? 'Phone Number ID' : ''));
            }

            // Validate the request
            $validated = $request->validate([
                'phone_number' => 'required|string',
                'header_text' => 'nullable|string|max:60',
                'body_text' => 'nullable|string|max:1024',
                'footer_text' => 'nullable|string|max:60'
            ]);

            // Get flow details to determine status
            $flowResponse = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get("{$baseUrl}/{$id}", [
                'fields' => 'status'
            ]);

            if (!$flowResponse->successful()) {
                throw new Exception('Failed to get flow status');
            }

            $flowData = $flowResponse->json();
            $isDraft = $flowData['status'] === 'DRAFT';

            // Split phone numbers by comma
            $phoneNumbers = array_filter(array_map('trim', explode(',', $validated['phone_number'])));
            
            if (empty($phoneNumbers)) {
                throw new Exception('No valid phone numbers provided');
            }
            
            // Validate each phone number
            foreach ($phoneNumbers as $phoneNumber) {
                if (!preg_match('/^[0-9]+$/', $phoneNumber)) {
                    throw new Exception('Invalid phone number format: ' . $phoneNumber);
                }
            }

            Log::info('Sending to multiple recipients', [
                'count' => count($phoneNumbers),
                'recipients' => $phoneNumbers
            ]);
            
            $results = [];
            $hasErrors = false;
            
            // Prepare common message template
            // For published flows, use the provided header, body, and footer text
            $headerText = $request->input('header_text', 'Hii');
            $bodyText = $request->input('body_text', 'Open the flow');
            $footerText = $request->input('footer_text', 'Choose an option');
            
            // For draft mode, get the preview token
            $previewToken = null;
            if ($isDraft) {
                $previewToken = $this->getFlowPreviewToken($id, $accessToken);
                
                if (empty($previewToken)) {
                    throw new Exception('Failed to get preview token for draft flow');
                }
            }

            // Send to each recipient
            foreach ($phoneNumbers as $phoneNumber) {
                // Prepare the payload for this recipient
                $payload = [
                    "messaging_product" => "whatsapp",
                    "to" => $phoneNumber,
                    "recipient_type" => "individual",
                    "type" => "interactive",
                    "interactive" => [
                        "type" => "flow",
                        "action" => [
                            "name" => "flow",
                            "parameters" => [
                                "flow_message_version" => "3",
                                "flow_action" => "navigate",
                                "flow_token" => "<FLOW_TOKEN>",
                                "flow_id" => $id
                            ]
                        ]
                    ]
                ];

                // Add different content based on flow status
                if ($isDraft) {
                    $payload["interactive"]["header"] = [
                        "type" => "text",
                        "text" => "Not shown in draft mode"
                    ];
                    $payload["interactive"]["body"] = [
                        "text" => "Not shown in draft mode"
                    ];
                    $payload["interactive"]["footer"] = [
                        "text" => "Not shown in draft mode"
                    ];
                    $payload["interactive"]["action"]["parameters"]["flow_cta"] = "Not shown in draft mode";
                    $payload["interactive"]["action"]["parameters"]["mode"] = "draft";
                    $payload['interactive']['action']['parameters']['flow_token'] = $previewToken;
                } else {
                    $payload["interactive"]["header"] = [
                        "type" => "text",
                        "text" => $headerText
                    ];
                    $payload["interactive"]["body"] = [
                        "text" => $bodyText
                    ];
                    $payload["interactive"]["footer"] = [
                        "text" => $footerText
                    ];
                    $payload["interactive"]["action"]["parameters"]["flow_cta"] = "Open Flow!";
                }

                Log::info('Sending WhatsApp message to recipient', [
                    'phone' => $phoneNumber,
                    'endpoint' => "{$baseUrl}/{$phoneNumberId}/messages"
                ]);

                // Make the API request to send the flow
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json'
                ])->post("{$baseUrl}/{$phoneNumberId}/messages", $payload);

                $responseData = $response->json();
                
                // Store the result
                $results[$phoneNumber] = [
                    'success' => $response->successful(),
                    'status' => $response->status(),
                    'response' => $responseData
                ];
                
                if (!$response->successful()) {
                    $hasErrors = true;
                }
                
                // Brief pause between requests to avoid rate limiting
                if (count($phoneNumbers) > 1) {
                    usleep(200000); // 200ms pause
                }
            }

            Log::info('All messages sent', [
                'results' => $results,
                'has_errors' => $hasErrors
            ]);

            // Prepare the response
            $responseMessage = count($phoneNumbers) > 1 
                ? 'Flow sent to ' . count($phoneNumbers) . ' recipients' 
                : 'Flow sent successfully';
                
            if ($hasErrors) {
                $errorCount = count(array_filter($results, function($result) {
                    return !$result['success'];
                }));
                
                $responseMessage = 'Flow sent with ' . $errorCount . ' errors out of ' . count($phoneNumbers) . ' recipients';
            }

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => !$hasErrors,
                    'message' => $responseMessage,
                    'results' => $results
                ]);
            }
            
            // For regular form submissions, redirect back with success or mixed message
            if ($hasErrors) {
                return back()->with('warning', $responseMessage);
            } else {
                return back()->with('success', $responseMessage);
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp Flow Send Exception', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // For regular form submissions, redirect back with error
            return back()->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Debug settings to find out why they're not being properly fetched
     *
     * @return \Illuminate\View\View
     */
    public function debugSettings()
    {
        // Get vendor ID
        $vendorId = getVendorId();
        
        // Direct DB query to see all WhatsApp related settings
        $allSettings = VendorSettingsModel::where('vendors__id', $vendorId)
            ->where(function($query) {
                $query->where('name', 'like', '%whatsapp%')
                    ->orWhere('name', 'like', '%waba%')
                    ->orWhere('name', 'like', '%phone%');
            })
            ->select('name', 'value')
            ->get()
            ->toArray();
        
        // Check specific keys we're trying to use
        $specificKeys = [
            'whatsapp_business_account_id' => getVendorSettings('whatsapp_business_account_id'),
            'whatsapp_access_token' => getVendorSettings('whatsapp_access_token') ? 'Set (value hidden)' : 'Not Set',
            'current_phone_number_id' => getVendorSettings('current_phone_number_id'),
            // Try alternative names
            'whatsapp_phone_numbers' => getVendorSettings('whatsapp_phone_numbers'),
            'whatsapp_phone_number_id' => getVendorSettings('whatsapp_phone_number_id')
        ];
        
        // Structure for debug output
        $debugData = [
            'vendor_id' => $vendorId,
            'specific_keys' => $specificKeys,
            'all_whatsapp_settings' => $allSettings
        ];
        
        Log::info('WhatsApp Flow Debug Settings', $debugData);
        
        return view('whatsapp-flows.debug', [
            'debugData' => $debugData
        ]);
    }

    /**
     * Debug API settings and test connection
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function debugApi(Request $request)
    {
        try {
            // Get all the relevant settings
            $vendorId = getVendorId();
            $wabaId = getVendorSettings('whatsapp_business_account_id');
            $accessToken = getVendorSettings('whatsapp_access_token');
            $phoneNumberId = getVendorSettings('current_phone_number_id');
            
            // Settings details
            $settingsInfo = [
                'vendor_id' => $vendorId,
                'waba_id' => $wabaId,
                'has_access_token' => !empty($accessToken),
                'phone_number_id' => $phoneNumberId
            ];
            
            // Log the settings info
            Log::info('Debug API settings', $settingsInfo);
            
            // Now test the API connections
            $apiChecks = [];
            
            // Check 1: Test access to WABA (if set)
            if (!empty($wabaId) && !empty($accessToken)) {
                try {
                    $baseUrl = "https://graph.facebook.com/v17.0";
                    $wabaUrl = "{$baseUrl}/{$wabaId}";
                    
                    $wabaResponse = Http::withHeaders([
                        'Authorization' => "Bearer {$accessToken}",
                    ])->get($wabaUrl);
                    
                    $apiChecks['waba_check'] = [
                        'success' => $wabaResponse->successful(),
                        'status' => $wabaResponse->status(),
                        'response' => $wabaResponse->successful() ? $wabaResponse->json() : null
                    ];
                } catch (\Exception $e) {
                    $apiChecks['waba_check'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Check 2: Test access to phone number (if set)
            if (!empty($phoneNumberId) && !empty($accessToken)) {
                try {
                    $baseUrl = "https://graph.facebook.com/v17.0";
                    $phoneUrl = "{$baseUrl}/{$phoneNumberId}";
                    
                    $phoneResponse = Http::withHeaders([
                        'Authorization' => "Bearer {$accessToken}",
                    ])->get($phoneUrl);
                    
                    $apiChecks['phone_check'] = [
                        'success' => $phoneResponse->successful(),
                        'status' => $phoneResponse->status(),
                        'response' => $phoneResponse->successful() ? $phoneResponse->json() : null
                    ];
                } catch (\Exception $e) {
                    $apiChecks['phone_check'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            // Check 3: Test formatting of URLs
            $urlFormats = [
                'base_url' => "https://graph.facebook.com/v17.0",
                'waba_flows_url' => "https://graph.facebook.com/v17.0/{$wabaId}/flows",
                'flow_detail_url' => "https://graph.facebook.com/v17.0/FLOW_ID_HERE",
                'messages_url' => "https://graph.facebook.com/v17.0/{$phoneNumberId}/messages"
            ];
            
            // Return detailed debug information
            return response()->json([
                'success' => true,
                'settings' => $settingsInfo,
                'api_checks' => $apiChecks,
                'url_formats' => $urlFormats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
} 