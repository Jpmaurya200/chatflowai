<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use App\Yantrana\Components\Contact\ContactEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamAssignmentNodeHandler extends BaseNodeHandler
{
    protected $contactEngine;
    protected $currentContext = [];
    protected $currentPayload = [];

    public function __construct()
    {
        parent::__construct();
        $this->contactEngine = resolve(ContactEngine::class);
    }

    /**
     * Process team assignment node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        try {
            $payload = $node['payload'] ?? [];
            $this->currentContext = $context;
            $this->currentPayload = $payload;
            
            $assignmentMessage = $payload['assignment_message'] ?? 'Assigning conversation to team member...';
            $assignedTeamMember = $payload['assigned_team_member'] ?? null;
            $assignedTeamMemberName = $payload['assigned_team_member_name'] ?? 'Team Member';

            // Extract contact UID from context - it can be in different locations
            $contactUid = $this->extractContactUid($context);

            Log::info('Processing team assignment node', [
                'node_id' => $node['id'] ?? 'unknown',
                'assigned_team_member' => $assignedTeamMember,
                'assigned_team_member_name' => $assignedTeamMemberName,
                'context_keys' => array_keys($context),
                'extracted_contact_uid' => $contactUid
            ]);

            // Validate assigned team member UID format
            if (empty($assignedTeamMember) || !is_string($assignedTeamMember) || strlen($assignedTeamMember) < 10) {
                Log::error('Invalid assigned team member UID', [
                    'assigned_team_member' => $assignedTeamMember
                ]);
                return [
                    'type' => 'text',
                    'text' => 'Invalid team member assignment. Please check configuration.',
                    'requires_input' => false,
                    'next_node' => null,
                    'node_id' => $node['id'] ?? 'unknown',
                    'assignment_completed' => false,
                    'error' => true,
                    'is_terminal' => true
                ];
            }

            // Validate contact_uid presence
            if (empty($contactUid) || !is_string($contactUid)) {
                Log::error('Missing or invalid contact_uid in context', [
                    'context_keys' => array_keys($context),
                    'contact_data' => isset($context['contact']) ? 'present' : 'missing',
                    'extracted_contact_uid' => $contactUid
                ]);
                return [
                    'type' => 'text',
                    'text' => 'Unable to assign conversation: contact information missing.',
                    'requires_input' => false,
                    'next_node' => null,
                    'node_id' => $node['id'] ?? 'unknown',
                    'assignment_completed' => false,
                    'error' => true,
                    'is_terminal' => true
                ];
            }

            // Perform the actual assignment
            $assignmentResult = $this->assignConversationToTeamMember(
                $contactUid,
                $assignedTeamMember
            );

            if ($assignmentResult['success']) {
                Log::info('Successfully assigned conversation to team member', [
                    'contact_uid' => $contactUid,
                    'assigned_team_member' => $assignedTeamMember,
                    'assigned_team_member_name' => $assignedTeamMemberName
                ]);
            } else {
                Log::error('Failed to assign conversation to team member', [
                    'contact_uid' => $contactUid,
                    'assigned_team_member' => $assignedTeamMember,
                    'error' => $assignmentResult['error'] ?? 'Unknown error'
                ]);
            }

            // Process dynamic variables in assignment message
            $variables = $this->getAllVariables($context);
            $processedMessage = $this->processDynamicVariables($assignmentMessage, $variables);

            // Return the assignment message to be sent to the user
            return [
                'type' => 'text',
                'text' => $processedMessage,
                'requires_input' => false,
                'next_node' => null, // Terminal node - no next node
                'node_id' => $node['id'] ?? 'unknown',
                'assignment_completed' => $assignmentResult['success'] ?? false,
                'assigned_team_member' => $assignedTeamMemberName,
                'is_terminal' => true,
                'error' => $assignmentResult['success'] ? false : true,
                'error_message' => $assignmentResult['error'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Error in team assignment node handler', [
                'node_id' => $node['id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'type' => 'text',
                'text' => 'Error occurred while assigning conversation to team member.',
                'requires_input' => false,
                'next_node' => null,
                'node_id' => $node['id'] ?? 'unknown',
                'error' => true,
                'is_terminal' => true
            ];
        }
    }

    /**
     * Assign conversation to team member using the exact same logic as live chat
     *
     * @param string $contactUid
     * @param string $teamMemberUid
     * @return array
     */
    private function assignConversationToTeamMember($contactUid, $teamMemberUid)
    {
        try {
            $vendorId = null;
            // Try to get vendor ID from context or payload
            if (isset($this->currentContext['vendor_id'])) {
                $vendorId = $this->currentContext['vendor_id'];
            } elseif (isset($this->currentPayload['vendor_id'])) {
                $vendorId = $this->currentPayload['vendor_id'];
            }
            // Fallback to getVendorId()
            if (!$vendorId) {
                $vendorId = getVendorId();
            }

            Log::info('Starting team assignment process', [
                'contact_uid' => $contactUid,
                'team_member_uid' => $teamMemberUid,
                'vendor_id' => $vendorId
            ]);

            // Validate vendor ID is available
            if (!$vendorId) {
                Log::error('No vendor ID available for team assignment', [
                    'contact_uid' => $contactUid,
                    'team_member_uid' => $teamMemberUid,
                    'context_keys' => array_keys($this->currentContext),
                    'payload_keys' => array_keys($this->currentPayload)
                ]);
                return [
                    'success' => false,
                    'error' => 'Vendor ID not available for assignment'
                ];
            }

            // Get user repository to validate team member directly
            $userRepository = resolve(\App\Yantrana\Components\User\Repositories\UserRepository::class);
            
            // Get all vendor messaging users for validation
            $vendorMessagingUsers = $userRepository->getVendorMessagingUsers($vendorId);
            $vendorMessagingUserUids = $vendorMessagingUsers->pluck('_uid')->toArray();
            
            Log::info('Vendor messaging users validation', [
                'vendor_id' => $vendorId,
                'team_member_uid' => $teamMemberUid,
                'available_messaging_users' => $vendorMessagingUserUids,
                'is_team_member_in_list' => in_array($teamMemberUid, $vendorMessagingUserUids)
            ]);
            
            // Validate that the team member has messaging permissions
            if (!in_array($teamMemberUid, $vendorMessagingUserUids)) {
                Log::error('Team member does not have messaging permissions', [
                    'team_member_uid' => $teamMemberUid,
                    'vendor_id' => $vendorId,
                    'available_messaging_users' => $vendorMessagingUserUids
                ]);
                
                // Try to fix permissions automatically
                $permissionFixed = $userRepository->ensureUserHasMessagingPermission($teamMemberUid, $vendorId);
                
                if (!$permissionFixed) {
                    return [
                        'success' => false,
                        'error' => 'Team member does not have messaging permissions and could not be automatically granted permissions'
                    ];
                }
                
                Log::info('Successfully fixed messaging permissions for team member', [
                    'team_member_uid' => $teamMemberUid,
                    'vendor_id' => $vendorId
                ]);
                
                // Re-fetch messaging users after fixing permissions
                $vendorMessagingUsers = $userRepository->getVendorMessagingUsers($vendorId);
                $vendorMessagingUserUids = $vendorMessagingUsers->pluck('_uid')->toArray();
            }

            // Get the team member details
            $teamMember = $userRepository->fetchIt([
                '_uid' => $teamMemberUid
            ]);
            
            if (__isEmpty($teamMember)) {
                Log::error('Team member not found', [
                    'team_member_uid' => $teamMemberUid
                ]);
                return [
                    'success' => false,
                    'error' => 'Team member not found'
                ];
            }

            // Get contact repository and update the contact directly
            $contactRepository = resolve(\App\Yantrana\Components\Contact\Repositories\ContactRepository::class);
            
            $contact = $contactRepository->fetchIt([
                '_uid' => $contactUid,
                'vendors__id' => $vendorId
            ]);
            
            if (__isEmpty($contact)) {
                Log::error('Contact not found', [
                    'contact_uid' => $contactUid,
                    'vendor_id' => $vendorId
                ]);
                return [
                    'success' => false,
                    'error' => 'Contact not found'
                ];
            }

            // Perform the assignment directly
            $updateResult = $contactRepository->updateIt($contact, [
                'assigned_users__id' => $teamMember->_id
            ]);

            if ($updateResult) {
                Log::info('Team assignment successful via direct update', [
                    'contact_uid' => $contactUid,
                    'team_member_uid' => $teamMemberUid,
                    'team_member_name' => $teamMember->full_name,
                    'vendor_id' => $vendorId
                ]);
                return [
                    'success' => true,
                    'message' => $teamMember->full_name . ' Assigned'
                ];
            } else {
                Log::error('Failed to update contact assignment', [
                    'contact_uid' => $contactUid,
                    'team_member_uid' => $teamMemberUid,
                    'vendor_id' => $vendorId
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to update contact assignment'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception in team assignment', [
                'contact_uid' => $contactUid,
                'team_member_uid' => $teamMemberUid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get next node ID (team assignment nodes are terminal)
     *
     * @param array $node
     * @param mixed $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        // Team assignment nodes are terminal - they end the flow
        return null;
    }

    /**
     * Team assignment nodes don't process user input
     *
     * @param array $node
     * @param mixed $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        // Team assignment nodes are terminal and don't process user input
        return [
            'context' => $context,
            'next_node' => null,
            'processed_input' => $userInput,
            'message' => 'This conversation has been assigned to a team member.'
        ];
    }

    /**
     * Check if this node type requires user input
     *
     * @return bool
     */
    public function requiresUserInput()
    {
        return false;
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'team_assignment';
    }

    /**
     * Validate team assignment node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['assigned_team_member'])) {
            $errors[] = 'Assigned team member is required';
        }
        
        return $errors;
    }

    /**
     * Extract contact UID from context - handles different context structures
     *
     * @param array $context
     * @return string|null
     */
    private function extractContactUid($context)
    {
        // Method 1: Direct contact_uid key
        if (!empty($context['contact_uid']) && is_string($context['contact_uid'])) {
            return $context['contact_uid'];
        }

        // Method 2: From contact model object in context
        if (isset($context['contact']) && is_array($context['contact'])) {
            // Handle Laravel model array structure
            foreach ($context['contact'] as $key => $contactData) {
                if (strpos($key, 'ContactModel') !== false && is_array($contactData)) {
                    if (!empty($contactData['_uid']) && is_string($contactData['_uid'])) {
                        return $contactData['_uid'];
                    }
                }
            }

            // Handle direct contact array
            if (!empty($context['contact']['_uid']) && is_string($context['contact']['_uid'])) {
                return $context['contact']['_uid'];
            }
        }

        // Method 3: From contact object if it's a model instance
        if (isset($context['contact']) && is_object($context['contact'])) {
            if (method_exists($context['contact'], 'getAttribute') && $context['contact']->getAttribute('_uid')) {
                return $context['contact']->getAttribute('_uid');
            }
            if (property_exists($context['contact'], '_uid')) {
                return $context['contact']->_uid;
            }
        }

        // Method 4: Look for contact UID in nested structures
        if (is_array($context)) {
            foreach ($context as $key => $value) {
                if (is_array($value) && isset($value['_uid']) && is_string($value['_uid']) && strlen($value['_uid']) > 10) {
                    // This might be a contact UID
                    return $value['_uid'];
                }
            }
        }

        return null;
    }
}