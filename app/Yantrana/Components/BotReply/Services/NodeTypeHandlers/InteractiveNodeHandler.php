<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use Illuminate\Support\Facades\Log;

/**
 * Handler for interactive type nodes (with buttons and lists)
 */
class InteractiveNodeHandler extends BaseNodeHandler
{
    /**
     * Process the interactive node
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'];

        // Get the actual reply text from the bot_replies table using node ID
        $text = $this->getBotReplyText($node['id'], $context);

        // If no reply text found in database, fallback to payload text
        if (empty($text)) {
            $text = $payload['text'] ?? '';
        }

        // PRIORITY: Check if this node has list_data in payload (from flow conversion)
        if (isset($payload['list_data']) && !empty($payload['list_data']['sections'])) {
            $payload['interactive_type'] = 'list';
            $payload['sections'] = $payload['list_data']['sections'];
            $payload['button_text'] = $payload['list_data']['button_text'] ?? 'Select Option';
        }

        // Determine interactive type with improved logic
        $interactiveType = $this->determineInteractiveType($payload);
        
        // Process dynamic variables using all available variables
        $allVariables = $this->getAllVariables($context);
        $processedText = $this->processDynamicVariables($text, $allVariables);

        $response = [
            'type' => 'interactive',
            'interactive_type' => $interactiveType,
            'text' => $processedText,
            'requires_input' => true,
            'node_id' => $node['id']
        ];

        // Log the interactive type determination for debugging
        \Illuminate\Support\Facades\Log::info('Processing interactive node', [
            'node_id' => $node['id'],
            'determined_type' => $interactiveType,
            'has_sections' => !empty($payload['sections']),
            'has_buttons' => !empty($payload['buttons']),
            'has_list_data' => !empty($payload['list_data']),
            'payload_interactive_type' => $payload['interactive_type'] ?? 'not_set'
        ]);

        // Process header and footer for all types
        if (isset($payload['header_text'])) {
            $response['header_text'] = $this->processDynamicVariables($payload['header_text'], $allVariables);
        }
        if (isset($payload['footer_text'])) {
            $response['footer_text'] = $this->processDynamicVariables($payload['footer_text'], $allVariables);
        }

        // Handle different interactive types
        switch ($interactiveType) {
            case 'list':
                // Handle list type interactive message
                $sections = $payload['sections'] ?? [];
                $processedSections = [];
                
                foreach ($sections as $section) {
                    $processedSection = [
                        'title' => $this->processDynamicVariables($section['title'] ?? '', $allVariables),
                        'rows' => []
                    ];
                    
                    foreach ($section['rows'] ?? [] as $row) {
                        $processedSection['rows'][] = [
                            'row_id' => $row['id'], // API expects 'row_id', not 'id'
                            'title' => $this->processDynamicVariables($row['title'], $allVariables),
                            'description' => $this->processDynamicVariables($row['description'] ?? '', $allVariables),
                            'next_node' => $row['next_node'] ?? null
                        ];
                    }
                    
                    $processedSections[] = $processedSection;
                }
                
                $response = [
                    'type' => 'interactive',
                    'interactive_type' => 'list',
                    'text' => $processedText,
                    'requires_input' => true,
                    'node_id' => $node['id'],
                    'body_text' => $processedText,
                    'list_data' => [
                        'button_text' => $this->processDynamicVariables($payload['button_text'] ?? 'Select an option', $allVariables),
                        'sections' => array_map(function($section) {
                            return [
                                'title' => $section['title'],
                                'rows' => array_map(function($row) {
                                    return [
                                        'id' => $row['id'] ?? $row['row_id'] ?? '',
                                        'title' => $row['title'],
                                        'description' => $row['description'] ?? ''
                                    ];
                                }, $section['rows'])
                            ];
                        }, $processedSections)
                    ]
                ];

                // Log list processing for debugging
                \Illuminate\Support\Facades\Log::info('Processed list-type interactive message', [
                    'node_id' => $node['id'],
                    'sections_count' => count($processedSections),
                    'total_rows' => array_sum(array_map(function($section) { return count($section['rows']); }, $processedSections)),
                    'button_text' => $response['list_data']['button_text']
                ]);
                break;

            case 'cta_url':
                // Handle CTA URL button type
                $response['cta_url'] = [
                    'display_text' => $this->processDynamicVariables($payload['button_display_text'] ?? '', $allVariables),
                    'url' => $this->processDynamicVariables($payload['button_url'] ?? '', $allVariables)
                ];
                break;

            case 'button':
            default:
                // Handle button type interactive message (default)
                $buttons = $payload['buttons'] ?? [];
                $processedButtons = [];
                
                foreach ($buttons as $button) {
                    $processedButtons[] = [
                        'id' => $button['id'],
                        'title' => $this->processDynamicVariables($button['title'], $allVariables),
                        'next_node' => $button['next_node'] ?? null
                    ];
                }
                
                $response['buttons'] = $processedButtons;

                // Log button processing for debugging
                \Illuminate\Support\Facades\Log::info('Processed button-type interactive message', [
                    'node_id' => $node['id'],
                    'buttons_count' => count($processedButtons)
                ]);
                break;
        }

        return $response;
    }

    /**
     * Determine the correct interactive type based on payload data
     *
     * @param array $payload
     * @return string
     */
    private function determineInteractiveType($payload)
    {
        // PRIORITY 1: If sections data exists and is not empty, it's a list type
        if (!empty($payload['sections']) && is_array($payload['sections'])) {
            // Validate that sections have rows
            foreach ($payload['sections'] as $section) {
                if (!empty($section['rows']) && is_array($section['rows'])) {
                    return 'list';
                }
            }
        }

        // PRIORITY 2: Check for CTA URL type
        if (!empty($payload['button_url']) || !empty($payload['button_display_text'])) {
            return 'cta_url';
        }

        // PRIORITY 3: Check for buttons data
        if (!empty($payload['buttons']) && is_array($payload['buttons'])) {
            return 'button';
        }

        // PRIORITY 4: Fallback to payload interactive_type if set
        if (!empty($payload['interactive_type'])) {
            return $payload['interactive_type'];
        }

        // DEFAULT: Return button type as fallback
        return 'button';
    }

    /**
     * Validate interactive node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];
        
        if (empty($payload['text'])) {
            $errors[] = 'Interactive text is required';
        }
        
        $interactiveType = $payload['interactive_type'] ?? 'button';
        
        // Validate header and footer if present
        if (isset($payload['header_text']) && empty($payload['header_text'])) {
            $errors[] = 'Header text cannot be empty if provided';
        }
        if (isset($payload['footer_text']) && empty($payload['footer_text'])) {
            $errors[] = 'Footer text cannot be empty if provided';
        }

        switch ($interactiveType) {
            case 'list':
                // Validate list type payload
                if (empty($payload['sections']) || !is_array($payload['sections'])) {
                    $errors[] = 'Sections array is required for list type interactive nodes';
                } else {
                    foreach ($payload['sections'] as $sectionIndex => $section) {
                        if (empty($section['rows']) || !is_array($section['rows'])) {
                            $errors[] = "Section $sectionIndex: Rows array is required";
                        } else {
                            foreach ($section['rows'] as $rowIndex => $row) {
                                if (empty($row['id'])) {
                                    $errors[] = "Section $sectionIndex, Row $rowIndex: ID is required";
                                }
                                if (empty($row['title'])) {
                                    $errors[] = "Section $sectionIndex, Row $rowIndex: Title is required";
                                }
                            }
                        }
                    }
                }
                
                if (empty($payload['button_text'])) {
                    $errors[] = 'Button text is required for list type interactive nodes';
                }
                break;

            case 'cta_url':
                // Validate CTA URL button type payload
                if (empty($payload['button_display_text'])) {
                    $errors[] = 'Button display text is required for CTA URL type';
                }
                if (empty($payload['button_url'])) {
                    $errors[] = 'Button URL is required for CTA URL type';
                } elseif (!filter_var($payload['button_url'], FILTER_VALIDATE_URL)) {
                    $errors[] = 'Invalid URL format for CTA URL button';
                }
                break;

            case 'button':
            default:
                // Validate button type payload (default)
                if (empty($payload['buttons']) || !is_array($payload['buttons'])) {
                    $errors[] = 'Buttons array is required for button type interactive nodes';
                } else {
                    foreach ($payload['buttons'] as $index => $button) {
                        if (empty($button['id'])) {
                            $errors[] = "Button $index: ID is required";
                        }
                        if (empty($button['title'])) {
                            $errors[] = "Button $index: Title is required";
                        }
                    }
                }
                break;
        }
        
        return $errors;
    }

    /**
     * Get the next node ID based on user selection
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        if (!$userInput) {
            return null;
        }

        $payload = $node['payload'];
        $interactiveType = $payload['interactive_type'] ?? 'button';

        switch ($interactiveType) {
            case 'list':
                // Handle list type selection
                $sections = $payload['sections'] ?? [];
                
                foreach ($sections as $section) {
                foreach ($section['rows'] ?? [] as $row) {
                    $titleMatch = strtolower(trim($row['title'])) === strtolower(trim($userInput));
                    $idMatch = (string)($row['id'] ?? $row['row_id']) === (string)$userInput;
                    
                    Log::info('Checking list selection match', [
                        'input' => $userInput,
                        'row_title' => $row['title'],
                        'row_id' => $row['id'] ?? $row['row_id'] ?? 'none',
                        'title_match' => $titleMatch,
                        'id_match' => $idMatch
                    ]);

                    if ($titleMatch || $idMatch) {
                        Log::info('List selection matched', [
                            'matched_row' => $row,
                            'next_node' => $row['next_node'] ?? null
                        ]);
                        return $row['next_node'] ?? null;
                    }
                }
                }
                break;

            case 'cta_url':
                // CTA URL buttons don't have next nodes - they redirect to external URLs
                return null;

            case 'button':
            default:
                // Handle button type selection (default)
                $buttons = $payload['buttons'] ?? [];

                foreach ($buttons as $button) {
                    $titleMatch = strtolower(trim($button['title'])) === strtolower(trim($userInput));
                    $idMatch = (string)$button['id'] === (string)$userInput;

                    if ($titleMatch || $idMatch) {
                        return $button['next_node'] ?? null;
                    }
                }
                break;
        }

        return null;
    }

    /**
     * Check if this node type requires user input
     *
     * @return bool
     */
    public function requiresUserInput()
    {
        return true;
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'interactive';
    }

    /**
     * Process user input for this interactive node
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        $payload = $node['payload'];
        $interactiveType = $payload['interactive_type'] ?? 'button';
        $selectedOption = null;
        $availableOptions = [];

        switch ($interactiveType) {
            case 'list':
                // Handle list type selection - check both sections and list_data.sections
                $sections = $payload['sections'] ?? $payload['list_data']['sections'] ?? [];
                
                Log::info('Processing list selection - sections data', [
                    'input' => $userInput,
                    'sections_count' => count($sections),
                    'payload_keys' => array_keys($payload),
                    'has_sections' => isset($payload['sections']),
                    'has_list_data_sections' => isset($payload['list_data']['sections'])
                ]);
                
                foreach ($sections as $section) {
                    foreach ($section['rows'] ?? [] as $row) {
                        $availableOptions[] = $row['title'];
                        
                        $titleMatch = strtolower(trim($row['title'])) === strtolower(trim($userInput));
                        $idMatch = strtolower((string)($row['id'] ?? $row['row_id'])) === strtolower((string)$userInput);
                        
                        Log::info('Processing list selection', [
                            'input' => $userInput,
                            'row' => $row,
                            'title_match' => $titleMatch,
                            'id_match' => $idMatch
                        ]);

                        if ($titleMatch || $idMatch) {
                            Log::info('Found matching list option', [
                                'matched_row' => $row,
                                'next_node' => $row['next_node'] ?? null
                            ]);
                            $selectedOption = $row;
                            break 2; // Break out of both loops
                        }
                    }
                }

                if (!$selectedOption) {
                    return [
                        'error' => 'Invalid list selection: ' . $userInput,
                        'context' => $context,
                        'next_node' => null,
                        'available_options' => $availableOptions
                    ];
                }

                return [
                    'context' => $context,
                    'next_node' => $selectedOption['next_node'] ?? null,
                    'selected_option' => $selectedOption,
                    'processed_input' => $userInput
                ];

            case 'cta_url':
                // CTA URL buttons don't process user input - they redirect to external URLs
                return [
                    'error' => 'CTA URL buttons do not accept user input',
                    'context' => $context,
                    'next_node' => null,
                    'processed_input' => $userInput
                ];

            case 'button':
            default:
                // Handle button type selection (default)
                $buttons = $payload['buttons'] ?? [];
                
                foreach ($buttons as $button) {
                    $availableOptions[] = $button['title'];
                    
                    $titleMatch = strtolower(trim($button['title'])) === strtolower(trim($userInput));
                    $idMatch = (string)$button['id'] === (string)$userInput;

                    if ($titleMatch || $idMatch) {
                        $selectedOption = $button;
                        break;
                    }
                }

                if (!$selectedOption) {
                    // Check for standard flow scenarios first
                    $standardResult = $this->handleStandardFlowScenarios($node, $userInput, $context);
                    if ($standardResult) {
                        return $standardResult;
                    }

                    // Handle no match scenario
                    $noMatchResult = $this->handleNoMatchScenario($node, $userInput, $context);
                    if ($noMatchResult) {
                        return $noMatchResult;
                    }

                    return [
                        'error' => 'Invalid button selection: ' . $userInput,
                        'context' => $context,
                        'next_node' => null,
                        'available_options' => $availableOptions
                    ];
                }

                return [
                    'context' => $context,
                    'next_node' => $selectedOption['next_node'] ?? null,
                    'selected_button' => $selectedOption,
                    'processed_input' => $userInput
                ];
        }
    }

    /**
     * Get available options for user (buttons or list items)
     *
     * @param array $node
     * @return array
     */
    public function getAvailableOptions($node)
    {
        $payload = $node['payload'];
        $interactiveType = $payload['interactive_type'] ?? 'button';
        $options = [];
        
        switch ($interactiveType) {
            case 'list':
                // Get list options
                $sections = $payload['sections'] ?? [];
                
                foreach ($sections as $section) {
                    foreach ($section['rows'] ?? [] as $row) {
                        $options[] = [
                            'id' => $row['id'],
                            'title' => $row['title'],
                            'description' => $row['description'] ?? '',
                            'value' => $row['id'],
                            'type' => 'list_item'
                        ];
                    }
                }
                break;

            case 'cta_url':
                // CTA URL buttons don't have selectable options
                $options[] = [
                    'id' => 'cta_url',
                    'title' => $payload['button_display_text'] ?? '',
                    'url' => $payload['button_url'] ?? '',
                    'value' => 'cta_url',
                    'type' => 'cta_url'
                ];
                break;

            case 'button':
            default:
                // Get button options (default)
                $buttons = $payload['buttons'] ?? [];
                
                foreach ($buttons as $button) {
                    $options[] = [
                        'id' => $button['id'],
                        'title' => $button['title'],
                        'value' => $button['id'],
                        'type' => 'button'
                    ];
                }
                break;
        }
        
        return $options;
    }

    /**
     * Get available button options for user (backward compatibility)
     *
     * @param array $node
     * @return array
     */
    public function getButtonOptions($node)
    {
        return $this->getAvailableOptions($node);
    }
}