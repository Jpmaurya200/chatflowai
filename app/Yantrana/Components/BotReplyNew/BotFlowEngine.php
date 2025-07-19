<?php
/**
* BotFlowEngine.php - Main component file
*
* This file is part of the BotReply component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\BotReply;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Yantrana\Base\BaseEngine;
use Illuminate\Database\Query\Builder;
use App\Yantrana\Components\BotReply\Repositories\BotFlowRepository;
use App\Yantrana\Components\BotReply\Repositories\BotReplyRepository;
use App\Yantrana\Components\BotReply\Interfaces\BotFlowEngineInterface;

class BotFlowEngine extends BaseEngine implements BotFlowEngineInterface
{
    /**
     * @var  BotFlowRepository $botFlowRepository - BotFlow Repository
     */
    protected $botFlowRepository;

    /**
     * @var  BotReplyRepository $botReplyRepository - BotReply Repository
     */
    protected $botReplyRepository;

    /**
      * Constructor
      *
      * @param  BotFlowRepository $botFlowRepository - BotFlow Repository
      * @param  BotReplyRepository $botReplyRepository - Bot Reply Repository
      *
      * @return  void
      *-----------------------------------------------------------------------*/

    public function __construct(
        BotFlowRepository $botFlowRepository,
        BotReplyRepository $botReplyRepository,
    ) {
        $this->botFlowRepository = $botFlowRepository;
        $this->botReplyRepository = $botReplyRepository;
    }


    /**
      * BotFlow datatable source
      *
      * @return  array
      *---------------------------------------------------------------- */
    public function prepareBotFlowDataTableSource()
    {
        $botFlowCollection = $this->botFlowRepository->fetchBotFlowDataTableSource();
        $orderStatuses = configItem('status_codes');
        // required columns for DataTables
        $requireColumns = [
            '_id',
            '_uid',
            'title',
            'start_trigger',
            'status' => function ($key) use ($orderStatuses) {
                return Arr::get($orderStatuses, $key['status']);
            },
        ];
        // prepare data for the DataTables
        return $this->dataTableResponse($botFlowCollection, $requireColumns);
    }


    /**
      * BotFlow delete process
      *
      * @param  mix $botFlowIdOrUid
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotFlowDelete($botFlowIdOrUid)
    {
        // fetch the record
        $botFlow = $this->botFlowRepository->fetchIt($botFlowIdOrUid);
        // check if the record found
        if (__isEmpty($botFlow)) {
            // if not found
            return $this->engineResponse(18, null, __tr('Bot Flow not found'));
        }
        // ask to delete the record
        if ($this->botFlowRepository->deleteIt($botFlow)) {
            // if successful
            return $this->engineResponse(1, null, __tr('Bot Flow deleted successfully'));
        }
        // if failed to delete
        return $this->engineResponse(2, null, __tr('Failed to delete BotFlow'));
    }

    /**
      * BotFlow create
      *
      * @param  array $inputData
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotFlowCreate($inputData)
    {
        $vendorId = getVendorId();
        // check the feature limit
        $vendorPlanDetails = vendorPlanDetails('bot_flows', $this->botFlowRepository->countIt([
            'vendors__id' => $vendorId,
        ]), $vendorId);
        if (!$vendorPlanDetails['is_limit_available']) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }
        // ask to add record
        if ($this->botFlowRepository->storeBotFlow($inputData)) {
            return $this->engineResponse(1, null, __tr('Bot Flow added.'));
        }

        return $this->engineResponse(2, null, __tr('Bot Flow not added.'));
    }

    /**
      * BotFlow prepare update data
      *
      * @param  mix $botFlowIdOrUid
      *
      * @return  EngineResponse
      *---------------------------------------------------------------- */

    public function prepareBotFlowUpdateData($botFlowIdOrUid)
    {
        $botFlow = $this->botFlowRepository->fetchIt($botFlowIdOrUid);

        // Check if $botFlow not exist then throw not found
        // exception
        if (__isEmpty($botFlow)) {
            return $this->engineResponse(18, null, __tr('Bot Flow not found.'));
        }

        return $this->engineResponse(1, $botFlow->toArray());
    }

    /**
      * BotFlow process update
      *
      * @param  mixed $botFlowIdOrUid
      * @param  array $inputData
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotFlowUpdate($botFlowIdOrUid, $request)
    {
        $vendorId = getVendorId();
        $botFlow = $this->botFlowRepository->fetchIt([
            '_uid' => $botFlowIdOrUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $botFlow not exist then throw not found
        // exception
        if (__isEmpty($botFlow)) {
            return $this->engineResponse(18, null, __tr('Bot Flow not found.'));
        }

        // validate for uniqueness
        $request->validate([
           "title" => [
               Rule::unique('bot_flows')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId))->ignore($botFlow->_id, '_id')
           ],
           'start_trigger' => [
               Rule::unique('bot_flows')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId))->ignore($botFlow->_id, '_id')
           ]
        ]);

        $updateData = [
            'title' => $request->title,
            'start_trigger' => $request->start_trigger,
            'status' => $request->status ? 1 : 2,
        ];

        // Update concerned start bots if start trigger updated
        if($botFlow->start_trigger != $request->start_trigger) {
            $this->botReplyRepository->updateItAll([
                'bot_flows__id' => $botFlow->_id,
                'reply_trigger' => $botFlow->start_trigger,
                'bot_replies__id' => null,
            ], [
                'reply_trigger' => $request->start_trigger,
            ]);
        }

        // Check if BotFlow updated
        if ($this->botFlowRepository->updateIt($botFlow, $updateData)) {
            return $this->engineResponse(1, null, __tr('Bot Flow updated.'));
        }

        return $this->engineResponse(14, null, __tr('Bot Flow not updated.'));
    }

    /**
     * Add UUID to output labels in flow chart data
     *
     * @param array $flowChartData
     * @return array
     */
    protected function addUuidToOutputLabels($flowChartData)
    {
        if (isset($flowChartData['operators'])) {
            foreach ($flowChartData['operators'] as &$operator) {
                if (isset($operator['properties']['outputs'])) {
                    foreach ($operator['properties']['outputs'] as &$output) {
                        if (!isset($output['label_id'])) {
                            $output['label_id'] = (string) Str::uuid();
                        }
                    }
                }
            }
        }
        return $flowChartData;
    }

    /**
     * Process Bot flow data update
     *
     * @param BaseRequestTwo $request
     * @return EngineResponse
     */
    // protected function addUuidToOutputLabels($flowChartData)
    // {
    //     if (isset($flowChartData['operators'])) {
    //         foreach ($flowChartData['operators'] as &$operator) {
    //             if (isset($operator['properties']['outputs'])) {
    //                 foreach ($operator['properties']['outputs'] as &$output) {
    //                     if (!isset($output['label_id'])) {
    //                         $output['label_id'] = (string) Str::uuid();
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $flowChartData;
    // }

    public function processBotFlowDataUpdate($request)
    {
        $vendorId = getVendorId();
        $botFlow = $this->botFlowRepository->fetchIt([
            '_uid' => $request->botFlowUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $botFlow not exist then throw not found
        // exception
        if (__isEmpty($botFlow)) {
            return $this->engineResponse(18, null, __tr('Bot Flow not found.'));
        }
        $updateData = [];
        $isTriggersReset = false;
        if($request->has('flow_chart_data')) {
            $flowChartData = $request->flow_chart_data;
            // Add UUID to output labels
            $flowChartData = $this->addUuidToOutputLabels($flowChartData);
            $flowChatLinks = $flowChartData['links'] ?? [];
            $flowChatOperators = $flowChartData['operators'] ?? [];
            $flowBots = $this->botReplyRepository->fetchItAll([
                'vendors__id' => $vendorId,
                'bot_flows__id' => $botFlow->_id,
            ]);
            if(!__isEmpty($flowBots)) {
                $flowBotsArray = $flowBots->keyBy('_uid');
                // clean up
                if(!__isEmpty($flowChatOperators)) {
                    foreach ($flowChatOperators as $flowChatOperatorKey => $flowChatOperatorValue) {
                        if(!($flowBotsArray[$flowChatOperatorKey] ?? null)) {
                            unset($flowChatOperators[$flowChatOperatorKey]);
                        }
                    }
                    $flowChartData['operators'] = $flowChatOperators;
                }
                $flowBotsForTheLinksUids = [];
                $botTriggers = [];
                foreach ($flowChatLinks as $link) {
                    $flowBotsForTheLinksUids[] = $link['toOperator'];
                    if(!isset($botTriggers[$link['toOperator']])) {
                        $botTriggers[$link['toOperator']] = [];
                    }
                    if($link['fromOperator'] != 'start') {
                        $fromBot = $flowBotsArray[$link['fromOperator']] ?? [];
                        if(!__isEmpty($fromBot)) {
                            $botButtons =  $fromBot->__data['interaction_message']['buttons'] ?? [];
                            $triggerSubject = null;
                            if(empty($botButtons)) {
                                $listDataSections =  $fromBot->__data['interaction_message']['list_data'] ?? [];
                                if(!empty($listDataSections)) {
                                    $listDataSections =  $fromBot->__data['interaction_message']['list_data'] ?? [];
                                    $triggerSubject = Arr::get($listDataSections, str_replace('___', '.', $link['fromConnector']));
                                }
                            } else {
                                $triggerSubject = Arr::get($botButtons, $link['fromConnector']);
                            }
                        }
                    } else {
                        $triggerSubject = $botFlow->start_trigger;
                    }
                    $toBot = $flowBotsArray[$link['toOperator']] ?? [];
                    if(!__isEmpty($toBot) and $triggerSubject) {
                        // collect for multiple triggers
                        $botTriggers[$link['toOperator']][] = $triggerSubject;
                        $this->botReplyRepository->updateIt($toBot, [
                            'reply_trigger' =>implode(',', ($botTriggers[$link['toOperator']] ?? [])),
                            'bot_replies__id' => $fromBot->_id ?? null,
                        ]);
                    }
                }
                $botsToResetTriggerUids = array_diff($flowBots->pluck('_uid')->toArray(), $flowBotsForTheLinksUids);
                if(!empty($botsToResetTriggerUids)) {
                    $isTriggersReset = $this->botReplyRepository->resetBotTriggers($botsToResetTriggerUids);
                }
            }
            $updateData['__data']['flow_builder_data'] = $flowChartData;

            // Automatically convert to new node structure
            try {
                $newFlowData = $this->convertToNewFlowStructure($flowChartData, $botFlow->_uid);
                $updateData['__data']['flow_nodes_data'] = $newFlowData;

                // Update goto node connections in bot replies
                $this->updateGotoNodeConnections($newFlowData, $botFlow->_id, $vendorId);

                \Illuminate\Support\Facades\Log::info('Auto-converted flow to new structure', [
                    'flow_id' => $botFlow->_uid,
                    'node_count' => count($newFlowData['nodes'] ?? [])
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to auto-convert flow to new structure', [
                    'flow_id' => $botFlow->_uid,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Handle new node-based flow data
        if($request->has('flow_nodes_data')) {
            $flowNodesData = $request->flow_nodes_data;

            // Validate the new flow structure
            $flowNodeService = new \App\Yantrana\Components\BotReply\Services\FlowNodeService();
            $validationErrors = $flowNodeService->validateFlowStructure($flowNodesData);

            if (!empty($validationErrors)) {
                return $this->engineResponse(2, null, 'Flow validation failed: ' . implode(', ', $validationErrors));
            }

            // Store the new flow structure
            $updateData['__data']['flow_nodes_data'] = $flowNodesData;

            // Process node-based flow logic
            $this->processNodeBasedFlowLogic($flowNodesData, $botFlow, $vendorId);
        }

        if($request->has('bot_flow_status')) {
            if($request->bot_flow_status) {
                $updateData['status'] = 1; // active
            } else {
                $updateData['status'] = 2; // inactive
            }
        }
        // Check if BotFlow updated
        if ($this->botFlowRepository->updateIt($botFlow, $updateData) or $isTriggersReset) {
            if($request->has('flow_chart_data')) {
                return $this->engineResponse(21, [
                    'reloadPage' => true,
                    'messageType' => 'success',
                ], __tr('Bot flow Data Updated'));
            }
            return $this->engineResponse(1, null, __tr('Bot flow Data Updated'));
        }
        // reloaded even not updated to prevent potential unsaved dialog issue
        return $this->engineResponse(21, [
            'reloadPage' => true,
            'messageType' => 'success',
        ], __tr('Bot flow Data Saved'));
    }

    /**
      * Prepare BotFlow Builder Data
      *
      * @param  mix $botFlowIdOrUid
      *
      * @return  EngineResponse
      *---------------------------------------------------------------- */

    public function prepareBotFlowBuilderData($botFlowIdOrUid)
    {
        $vendorId = getVendorId();
        $botFlow = $this->botFlowRepository->fetchIt([
          '_uid' => $botFlowIdOrUid,
          'vendors__id' => $vendorId,
        ]);
        // Check if $botFlow not exist then throw not found
        if (__isEmpty($botFlow)) {
            return $this->engineResponse(18, null, __tr('Bot Flow not found.'));
        }

        $flowBots = $this->botReplyRepository->fetchItAll([
              'bot_flows__id' => $botFlow->_id,
              'vendors__id' => $vendorId,
        ]);

        return $this->engineResponse(1, [
          'botFlow' => $botFlow,
          'flowBots' => $flowBots,
        ]);
    }

    /**
     * Convert old flow structure to new node-based structure
     *
     * @param array $oldFlowData
     * @param string $flowId
     * @return array
     */
    public function convertToNewFlowStructure($oldFlowData, $flowId)
    {
        $nodes = [];
        $operators = $oldFlowData['operators'] ?? [];
        $links = $oldFlowData['links'] ?? [];

        // Create a mapping of operators to their next nodes based on links
        $nextNodeMap = [];
        foreach ($links as $link) {
            $fromOperator = $link['fromOperator'];
            $toOperator = $link['toOperator'];
            $fromConnector = $link['fromConnector'];

            if (!isset($nextNodeMap[$fromOperator])) {
                $nextNodeMap[$fromOperator] = [];
            }
            $nextNodeMap[$fromOperator][$fromConnector] = $toOperator;
        }

        // Convert operators to nodes
        foreach ($operators as $operatorId => $operator) {
            if ($operatorId === 'start') {
                continue; // Skip start operator as it's not a node
            }

            $nodeType = $this->determineNodeType($operator);
            $payload = $this->extractNodePayload($operator, $nextNodeMap[$operatorId] ?? []);

            // Get bot reply data for enhanced payload information
            $botReply = $this->botReplyRepository->fetchIt([
                '_uid' => $operatorId,
                'vendors__id' => getVendorId(),
            ]);

            if (!__isEmpty($botReply)) {
                // Check for goto_message data
                if (isset($botReply->__data['goto_message'])) {
                    $nodeType = 'goto';
                    $payload = [
                        'text' => $operator['properties']['title'] ?? '',
                        'redirect_to_node' => $botReply->__data['goto_message']['redirect_to_node'] ?? null
                    ];
                }
                // Check for question_message data
                elseif (isset($botReply->__data['question_message'])) {
                    $nodeType = 'question';
                    $questionData = $botReply->__data['question_message'];
                    $payload = array_merge($payload, [
                        'variable_name' => $questionData['variable_name'] ?? null,
                        'conditional_flows' => $questionData['conditional_flows'] ?? [],
                        'default_next_node' => $questionData['default_next_node'] ?? null,
                        'input_type' => $questionData['input_type'] ?? 'text',
                        'is_required' => $questionData['is_required'] ?? true
                    ]);
                }
            }

            $node = [
                'id' => $operatorId,
                'type' => $nodeType,
                'payload' => $payload,
                'position' => [
                    'x' => $operator['left'] ?? 100,
                    'y' => $operator['top'] ?? 100
                ]
            ];

            $nodes[] = $node;
        }

        return [
            'flow_id' => $flowId,
            'nodes' => $nodes
        ];
    }

    /**
     * Determine node type based on operator properties
     *
     * @param array $operator
     * @return string
     */
    private function determineNodeType($operator)
    {
        $properties = $operator['properties'] ?? [];
        $outputs = $properties['outputs'] ?? [];

        // Check if it has a goto output - goto node
        if (count($outputs) === 1) {
            $outputKeys = array_keys($outputs);
            $firstOutput = $outputs[$outputKeys[0]];
            $label = $firstOutput['label'] ?? '';

            if (strtolower($label) === 'redirect') {
                return 'goto';
            }
        }

        // Check if it has multiple outputs (buttons) - interactive node
        if (count($outputs) > 1) {
            return 'interactive';
        }

        // Check if it has a single output with specific labels that indicate message type
        if (count($outputs) === 1) {
            $outputKeys = array_keys($outputs);
            $firstOutput = $outputs[$outputKeys[0]];
            $label = $firstOutput['label'] ?? '';

            // If the output label is "Continue" or similar, it's likely a message node
            if (in_array(strtolower($label), ['continue', 'next', 'ok', 'proceed'])) {
                return 'message';
            }

            // Otherwise, it's interactive with a single button
            return 'interactive';
        }

        // No outputs means it's a terminal message node
        return 'message';
    }

    /**
     * Extract node payload from operator properties
     *
     * @param array $operator
     * @param array $nextNodes
     * @return array
     */
    private function extractNodePayload($operator, $nextNodes)
    {
        $properties = $operator['properties'] ?? [];
        $outputs = $properties['outputs'] ?? [];
        $payload = [
            'text' => $properties['title'] ?? ''
        ];

        // Check if it's a goto node (by checking for goto_internal connector or redirect label)
        if (count($outputs) === 1) {
            $outputKeys = array_keys($outputs);
            $firstOutput = $outputs[$outputKeys[0]];
            $label = $firstOutput['label'] ?? '';

            if (strtolower($label) === 'redirect') {
                $payload['redirect_to_node'] = !empty($nextNodes) ? reset($nextNodes) : null;
                return $payload;
            }
        }

        // Check for internal goto connections
        if (isset($nextNodes['goto_internal'])) {
            $payload['redirect_to_node'] = $nextNodes['goto_internal'];
            return $payload;
        }

        // If it has multiple outputs or specific button-like outputs, treat as interactive
        if (count($outputs) > 1 || $this->hasButtonLikeOutputs($outputs)) {
            $buttons = [];
            foreach ($outputs as $outputId => $output) {
                // Skip simple_output for message nodes and goto_output for goto nodes
                if (in_array($outputId, ['simple_output', 'goto_output'])) {
                    continue;
                }

                $buttons[] = [
                    'id' => $outputId,
                    'title' => $output['label'] ?? '',
                    'next_node' => $nextNodes[$outputId] ?? null
                ];
            }

            if (!empty($buttons)) {
                $payload['buttons'] = $buttons;
            } else {
                // If no buttons after filtering, treat as message with next_node
                $payload['next_node'] = !empty($nextNodes) ? reset($nextNodes) : null;
            }
        } else {
            // Simple message node with single next node
            $payload['next_node'] = !empty($nextNodes) ? reset($nextNodes) : null;
        }

        return $payload;
    }

    /**
     * Check if outputs are button-like (not simple continue buttons)
     *
     * @param array $outputs
     * @return bool
     */
    private function hasButtonLikeOutputs($outputs)
    {
        foreach ($outputs as $outputId => $output) {
            $label = strtolower($output['label'] ?? '');

            // If it's not a simple continue-type button, it's interactive
            if (!in_array($label, ['continue', 'next', 'ok', 'proceed']) && $outputId !== 'simple_output') {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert new flow structure back to old format for compatibility
     *
     * @param array $newFlowData
     * @return array
     */
    public function convertToOldFlowStructure($newFlowData)
    {
        $operators = [
            'start' => [
                'top' => 10,
                'left' => 10,
                'properties' => [
                    'title' => 'Start ->',
                    'outputs' => [
                        'start_output' => [
                            'label' => 'Start'
                        ]
                    ]
                ]
            ]
        ];
        $links = [];

        $nodes = $newFlowData['nodes'] ?? [];

        // Convert nodes to operators
        foreach ($nodes as $node) {
            $operators[$node['id']] = [
                'top' => $node['position']['y'] ?? 100,
                'left' => $node['position']['x'] ?? 100,
                'properties' => [
                    'title' => $node['payload']['text'] ?? '',
                    'inputs' => [
                        'input' => [
                            'label' => '-->'
                        ]
                    ],
                    'outputs' => []
                ]
            ];

            // Handle different node types
            if ($node['type'] === 'interactive' && isset($node['payload']['buttons'])) {
                foreach ($node['payload']['buttons'] as $button) {
                    $operators[$node['id']]['properties']['outputs'][$button['id']] = [
                        'label' => $button['title']
                    ];

                    // Create link if next_node is specified
                    if (!empty($button['next_node'])) {
                        $links[] = [
                            'fromOperator' => $node['id'],
                            'fromConnector' => $button['id'],
                            'toOperator' => $button['next_node'],
                            'toConnector' => 'input'
                        ];
                    }
                }
            } elseif ($node['type'] === 'goto' && !empty($node['payload']['redirect_to_node'])) {
                // Goto node - no visible outputs, but maintain internal connection data
                // Don't add any outputs to prevent manual connections

                // Add internal link for data consistency (won't be visible)
                $links[] = [
                    'fromOperator' => $node['id'],
                    'fromConnector' => 'goto_internal',
                    'toOperator' => $node['payload']['redirect_to_node'],
                    'toConnector' => 'input'
                ];
            } elseif (!empty($node['payload']['next_node'])) {
                // Simple node with single next connection
                $operators[$node['id']]['properties']['outputs']['output'] = [
                    'label' => 'Next'
                ];

                $links[] = [
                    'fromOperator' => $node['id'],
                    'fromConnector' => 'output',
                    'toOperator' => $node['payload']['next_node'],
                    'toConnector' => 'input'
                ];
            }
        }

        return [
            'operators' => $operators,
            'links' => $links
        ];
    }

    /**
     * Create a sample flow with the new structure
     *
     * @param string $flowId
     * @return array
     */
    public function createSampleNewFlow($flowId)
    {
        return [
            'flow_id' => $flowId,
            'nodes' => [
                [
                    'id' => 'fe635e1b-fd55-4d65-bbfb-bb3122c61c60',
                    'type' => 'question',
                    'payload' => [
                        'text' => 'What is your name?',
                        'next_node' => '032ab0e5-e987-4bc4-a6ad-3169f3cb73ec',
                        'variable_name' => 'user_name'
                    ],
                    'position' => ['x' => 100, 'y' => 300]
                ],
                [
                    'id' => '032ab0e5-e987-4bc4-a6ad-3169f3cb73ec',
                    'type' => 'question',
                    'payload' => [
                        'text' => 'What is your favorite subject?',
                        'next_node' => 'cf19e0d8-7b9c-4c99-a416-e1e395163b58',
                        'variable_name' => 'fav_subject'
                    ],
                    'position' => ['x' => 100, 'y' => 500]
                ],
                [
                    'id' => 'cf19e0d8-7b9c-4c99-a416-e1e395163b58',
                    'type' => 'interactive',
                    'payload' => [
                        'text' => 'Ready to test your knowledge?',
                        'buttons' => [
                            [
                                'id' => 'a1f27c9a-d939-4aed-af4f-5d7dfc2adf2c',
                                'title' => 'Yes, Let\'s Go!',
                                'next_node' => '918fef58-68f4-4a9f-9822-5706db203c94'
                            ],
                            [
                                'id' => '9c32e689-0f70-4985-bcf6-2e34e0b19d95',
                                'title' => 'Maybe Later',
                                'next_node' => '1a5e61a4-e207-47f0-92dc-89946adba72f'
                            ],
                            [
                                'id' => 'b8cd9e20-d3f0-4b09-bf79-cb4ad0d3cf35',
                                'title' => 'Restart Info',
                                'next_node' => 'b7468e4a-8bd3-49aa-b75f-b8ea174042f0'
                            ]
                        ]
                    ],
                    'position' => ['x' => 100, 'y' => 700]
                ],
                [
                    'id' => 'b7468e4a-8bd3-49aa-b75f-b8ea174042f0',
                    'type' => 'goto',
                    'payload' => [
                        'redirect_to_node' => 'fe635e1b-fd55-4d65-bbfb-bb3122c61c60'
                    ],
                    'position' => ['x' => 350, 'y' => 700]
                ],
                [
                    'id' => '1a5e61a4-e207-47f0-92dc-89946adba72f',
                    'type' => 'message',
                    'payload' => [
                        'text' => 'Thanks for joining. You can always come back to take the quiz!'
                    ],
                    'position' => ['x' => 350, 'y' => 900]
                ]
            ]
        ];
    }

    /**
     * Process node-based flow logic
     *
     * @param array $flowNodesData
     * @param object $botFlow
     * @param int $vendorId
     * @return void
     */
    private function processNodeBasedFlowLogic($flowNodesData, $botFlow, $vendorId)
    {
        $nodes = $flowNodesData['nodes'] ?? [];
        $flowBots = $this->botReplyRepository->fetchItAll([
            'vendors__id' => $vendorId,
            'bot_flows__id' => $botFlow->_id,
        ]);
        $flowBotsArray = $flowBots->keyBy('_uid');

        // Process each node to set up triggers and connections
        foreach ($nodes as $node) {
            $nodeId = $node['id'];

            // Find corresponding bot reply
            $botReply = $flowBotsArray[$nodeId] ?? null;
            if (!$botReply) {
                continue;
            }

            // Set up triggers based on node type and connections
            $this->setupNodeTriggers($node, $botReply, $botFlow, $nodes);
        }
    }

    /**
     * Setup triggers for a specific node
     *
     * @param array $node
     * @param object $botReply
     * @param object $botFlow
     * @param array $allNodes
     * @return void
     */
    private function setupNodeTriggers($node, $botReply, $botFlow, $allNodes)
    {
        $triggers = [];

        // Find nodes that point to this node
        foreach ($allNodes as $sourceNode) {
            $sourcePayload = $sourceNode['payload'];

            // Check direct next_node references
            if (isset($sourcePayload['next_node']) && $sourcePayload['next_node'] === $node['id']) {
                $triggers[] = $this->getTriggerForNode($sourceNode, $botFlow);
            }

            // Check button next_node references
            if (isset($sourcePayload['buttons'])) {
                foreach ($sourcePayload['buttons'] as $button) {
                    if (isset($button['next_node']) && $button['next_node'] === $node['id']) {
                        $triggers[] = $button['title'];
                    }
                }
            }

            // Check goto redirect references
            if (isset($sourcePayload['redirect_to_node']) && $sourcePayload['redirect_to_node'] === $node['id']) {
                $triggers[] = $this->getTriggerForNode($sourceNode, $botFlow);
            }
        }

        // Update bot reply with triggers
        if (!empty($triggers)) {
            $this->botReplyRepository->updateIt($botReply, [
                'reply_trigger' => implode(',', array_unique($triggers))
            ]);
        }
    }

    /**
     * Get trigger text for a node
     *
     * @param array $node
     * @param object $botFlow
     * @return string
     */
    private function getTriggerForNode($node, $botFlow)
    {
        // For the first node in flow, use the flow start trigger
        if ($this->isFirstNode($node, $botFlow)) {
            return $botFlow->start_trigger;
        }

        // For other nodes, use the node text or a default
        return $node['payload']['text'] ?? 'trigger_' . $node['id'];
    }

    /**
     * Check if node is the first node in the flow
     *
     * @param array $node
     * @param object $botFlow
     * @return bool
     */
    private function isFirstNode($node, $botFlow)
    {
        // This would need more sophisticated logic to determine the first node
        // For now, we'll use a simple heuristic based on position
        // Could also check if this node is referenced by the flow start_trigger
        return $node['position']['y'] <= 300; // Nodes near the top are likely first
    }

    /**
     * Update goto node connections in bot replies
     *
     * @param array $flowData
     * @param int $botFlowId
     * @param int $vendorId
     * @return void
     */
    private function updateGotoNodeConnections($flowData, $botFlowId, $vendorId)
    {
        $nodes = $flowData['nodes'] ?? [];

        foreach ($nodes as $node) {
            if ($node['type'] === 'goto' && !empty($node['payload']['redirect_to_node'])) {
                $botReply = $this->botReplyRepository->fetchIt([
                    '_uid' => $node['id'],
                    'bot_flows__id' => $botFlowId,
                    'vendors__id' => $vendorId,
                ]);

                if (!__isEmpty($botReply)) {
                    // Get target node name
                    $targetNode = $this->botReplyRepository->fetchIt([
                        '_uid' => $node['payload']['redirect_to_node'],
                        'bot_flows__id' => $botFlowId,
                        'vendors__id' => $vendorId,
                    ]);

                    $targetNodeName = !__isEmpty($targetNode) ? $targetNode->name : 'Unknown Node';

                    // Update the bot reply with goto_message data
                    $botData = $botReply->__data ?? [];
                    $botData['goto_message'] = [
                        'redirect_to_node' => $node['payload']['redirect_to_node'],
                        'target_node_name' => $targetNodeName,
                    ];

                    $this->botReplyRepository->updateIt($botReply, [
                        '__data' => $botData
                    ]);
                }
            }
        }
    }

}
