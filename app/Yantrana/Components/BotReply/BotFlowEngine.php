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

use Illuminate\Support\Facades\Log;


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
            'trigger_type',
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



    public function processBotFlowClone($botFlowIdOrUid)
{
    $vendorId = getVendorId();

    // fetch the record
    $botFlow = $this->botFlowRepository->fetchIt([
        '_uid' => $botFlowIdOrUid,
        'vendors__id' => $vendorId,
    ]);

    if (__isEmpty($botFlow)) {
        return $this->engineResponse(18, [
            'botFlowUid' => $botFlowIdOrUid
        ], __tr('Bot Flow not found'));
    }

    // Create new UID for the cloned flow
    $newBotFlowUid = Str::uuid();
    $newBotFlow = $botFlow->replicate();
    $newBotFlow->title = $botFlow->title . ' - Copy';
    $newBotFlow->_uid = $newBotFlowUid;
    $newBotFlow->status = 2; 
    $newBotFlow->vendors__id = $vendorId;

    // Clone flow builder data and update context_flow_uid
    $flowData = $botFlow->__data;
    if ($flowData) {
        if (isset($flowData['flow_builder_data']['operators'])) {
            foreach ($flowData['flow_builder_data']['operators'] as &$operator) {
                if (isset($operator['properties']['body'])) {
                    $bodyHtml = $operator['properties']['body'];

                    // Replace old context_flow_uid with new flow UID
                    $bodyHtml = preg_replace(
                        '/"context_flow_uid":\s*"[^"]+"/',
                        '"context_flow_uid": "'.$newBotFlowUid.'"',
                        $bodyHtml
                    );

                    $operator['properties']['body'] = $bodyHtml;
                }
            }
        }
        $newBotFlow->__data = $flowData;
    }

    if ($newBotFlow->save()) {
        // ✅ Clone associated bot replies
        $this->cloneBotFlowReplies($botFlow->_id, $newBotFlow->_id, $vendorId);
        return $this->engineResponse(1, [
            'botFlowUid' => $newBotFlowUid
        ], __tr('Bot Flow cloned successfully'));
    }

    return $this->engineResponse(2, [
        'botFlowUid' => $botFlowIdOrUid
    ], __tr('Failed to clone Bot Flow'));
}

/**
 * Clone bot replies when a flow is cloned
 */
/**
 * Clone bot replies when a flow is cloned
 */
private function cloneBotFlowReplies($originalFlowId, $newFlowId, $vendorId)
    {
        // Get all bot replies associated with the original flow
        $originalBotReplies = $this->botReplyRepository->fetchItAll([
            'vendors__id' => $vendorId,
            'bot_flows__id' => $originalFlowId,
        ]);

        // Fetch the cloned flow
        $botFlow = $this->botFlowRepository->fetchIt([
            'vendors__id' => $vendorId,
            '_id' => $newFlowId,
        ]);

        // Work with the flow's __data
        $flowData = $botFlow->__data;

        if (is_string($flowData)) {
            $flowData = json_decode($flowData, true);
        }

        // Build map of old reply ID -> new reply ID
        $botReplyIdMap = [];
        $newlyCreatedReplies = [];

        foreach ($originalBotReplies as $originalBotReply) {
            // Generate the new UID once
            $newBotReplyUid = (string) Str::uuid();
        
            // Clone reply
            $newBotReply = $originalBotReply->replicate();
            $newBotReply->name = $originalBotReply->name . ' - Copy';
            $newBotReply->_uid = $newBotReplyUid; // force assign
            $newBotReply->bot_flows__id = $newFlowId;
            $newBotReply->status = 2; 
            $newBotReply->vendors__id = $vendorId;
            $newBotReply->save();
        
            // �� always use the one stored in DB (avoid mismatch)
            $savedUid = $newBotReply->_uid;
            // Track mapping from original DB id to newly created DB id
            $botReplyIdMap[$originalBotReply->_id] = $newBotReply->_id;
            $newlyCreatedReplies[] = $newBotReply;
        
            
        
            // Replace old UID with new UID in flow JSON
            array_walk_recursive($flowData, function (&$value) use ($originalBotReply, $savedUid) {
                if ($value === $originalBotReply->_uid) {
                    $value = $savedUid;
                }
            });

            // 🔥 Replace operator keys in flow_builder_data.operators
            if (isset($flowData['flow_builder_data']['operators'])) {
                $operators = $flowData['flow_builder_data']['operators'];
                
                // Check if the original UID exists as an operator key
                if (isset($operators[$originalBotReply->_uid])) {
                    // Get the operator data
                    $operatorData = $operators[$originalBotReply->_uid];
                    
                    // Remove the old operator with original UID
                    unset($flowData['flow_builder_data']['operators'][$originalBotReply->_uid]);
                    
                    // Add the operator with new UID as key
                    $flowData['flow_builder_data']['operators'][$savedUid] = $operatorData;
                }
            }
        }
        
        // After all replies are cloned, update bot_replies__id on new replies using the map
        foreach ($newlyCreatedReplies as $clonedReply) {
            $oldLinkedId = $clonedReply->bot_replies__id ?? null;
            if (!empty($oldLinkedId) && isset($botReplyIdMap[$oldLinkedId])) {
                $clonedReply->bot_replies__id = $botReplyIdMap[$oldLinkedId];
                $clonedReply->save();
            }
        }
        
        // ✅ Update flow_id in flow_nodes_data to match new flow UID
        if (isset($flowData['flow_nodes_data']['flow_id'])) {
            $flowData['flow_nodes_data']['flow_id'] = $botFlow->_uid; // <- use cloned flow UID
        }

        $botFlow->__data = $flowData;
        $botFlow->save();
    }



    /**
      * BotFlow export process
      *
      * @param  mix $botFlowIdOrUid
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotFlowExport($botFlowIdOrUid)
    {
        $vendorId = getVendorId();
        // fetch the record
        $botFlow = $this->botFlowRepository->fetchIt([
            '_uid' => $botFlowIdOrUid,
            'vendors__id' => $vendorId,
        ]);
        // check if the record found
        if (__isEmpty($botFlow)) {
            // if not found
            return $this->engineResponse(18, [
                'botFlowUid' => $botFlowIdOrUid
            ], __tr('Bot Flow not found'));
        }

        // Prepare export data
        $exportData = $this->prepareBotFlowExportData($botFlow);

        // Generate filename
        $filename = 'bot-flow-' . Str::slug($botFlow->title) . '-' . date('Y-m-d-H-i-s') . '.json';

        // Return success with export data
        return $this->engineResponse(1, [
            'exportData' => $exportData,
            'filename' => $filename,
            'botFlowTitle' => $botFlow->title
        ], __tr('Bot Flow export data prepared successfully'));
    }

    /**
     * Prepare bot flow export data
     *
     * @param object $botFlow
     * @return array
     */
    private function prepareBotFlowExportData($botFlow)
    {
        // Get all bot replies associated with this flow
        $botReplies = $this->botReplyRepository->fetchItAll([
            'vendors__id' => $botFlow->vendors__id,
            'bot_flows__id' => $botFlow->_id,
        ]);

        // Prepare bot flow data (remove vendor-specific and system fields)
        $flowData = [
            'status' => 2,
            'whatsapp_flow_id' => $botFlow->whatsapp_flow_id,
            'whatsapp_sync_status' => $botFlow->whatsapp_sync_status,
            'whatsapp_sync_at' => $botFlow->whatsapp_sync_at,
            'whatsapp_meta_data' => $botFlow->whatsapp_meta_data,
            'title' => $botFlow->title,
            'trigger_type' => $botFlow->trigger_type,
            'start_trigger' => $botFlow->start_trigger,
            '_uid' => $botFlow->_uid,
            'id' => $botFlow->_id,
            'vendors__id' => $botFlow->vendors__id,
            '__data' => $botFlow->__data,
           
        ];

        // Prepare bot replies data (remove vendor-specific and system fields)
        $repliesData = [];
        foreach ($botReplies as $botReply) {
            $replyData = [
                'uid' => $botReply->_uid,
                'id' => $botReply->_id,
                'bot_replies__id' => $botReply->bot_replies__id,
                'status' => $botReply->status,
                'name' => $botReply->name,
                'reply_text' => $botReply->reply_text,
                'trigger_type' => $botReply->trigger_type,
                'reply_trigger' => $botReply->reply_trigger,
                'priority_index' => $botReply->priority_index,
                '__data' => $botReply->__data,

                // 'bot_data' => $this->sanitizeBotReplyData($botReply->__data),
            ];
            $repliesData[] = $replyData;
        }

        // Prepare final export structure
        $exportData = [
            'export_version' => '1.0',
            'export_date' => now()->toISOString(),
            'bot_flow' => $flowData,
            'bot_replies' => $repliesData,
        ];

        return $exportData;
    }

    // /**
    //  * Sanitize bot reply data for export (remove user-specific data)
    //  *
    //  * @param array $botData
    //  * @return array
    //  */
    // private function sanitizeBotReplyData($botData)
    // {
    //     if (!$botData) {
    //         return [];
    //     }

    //     // Create a copy to avoid modifying original data
    //     $sanitizedData = $botData;

    //     // Remove user-specific data that shouldn't be exported
    //     if (isset($sanitizedData['interaction_message'])) {
    //         // Reset buttons to empty array (user will need to reconfigure)
    //         if (isset($sanitizedData['interaction_message']['buttons'])) {
    //             $sanitizedData['interaction_message']['buttons'] = [];
    //         }

    //         // Reset list data but keep structure
    //         if (isset($sanitizedData['interaction_message']['list_data'])) {
    //             $sanitizedData['interaction_message']['list_data'] = [
    //                 'button_text' => $sanitizedData['interaction_message']['list_data']['button_text'] ?? '',
    //                 'sections' => [], // Reset sections
    //             ];
    //         }
    //     }

    //     // Remove any vendor-specific configurations
    //     unset($sanitizedData['vendor_id']);
    //     unset($sanitizedData['user_id']);
    //     unset($sanitizedData['created_at']);
    //     unset($sanitizedData['updated_at']);

    //     return $sanitizedData;
    // }

    /**
      * BotFlow import process
      *
      * @param  object $uploadedFile
      *
      * @return  array
      *---------------------------------------------------------------- */

    public function processBotFlowImport($uploadedFile)
    {
        $vendorId = getVendorId();

        try {
            // Read and decode JSON file
            $jsonContent = file_get_contents($uploadedFile->getRealPath());
            $importData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->engineResponse(2, [], __tr('Invalid JSON file format'));
            }

            // Validate import data structure
            $validationResult = $this->validateImportData($importData);
            if (!$validationResult['valid']) {
                return $this->engineResponse(2, [], $validationResult['message']);
            }

            // Check if bot flow with same title already exists
            $existingFlow = $this->botFlowRepository->fetchIt([
                'title' => $importData['bot_flow']['title'],
                'vendors__id' => $vendorId,
            ]);

            if (!__isEmpty($existingFlow)) {
                // Add suffix to make title unique
                $importData['bot_flow']['title'] = $importData['bot_flow']['title'] . ' - Imported';
            }

            // Create the bot flow
            $botFlowResult = $this->createBotFlowFromImport($importData['bot_flow'], $vendorId);
            if (!$botFlowResult['success']) {
                return $this->engineResponse(2, [], $botFlowResult['message']);
            }

            // Create associated bot replies
            $botRepliesResult = $this->createBotRepliesFromImport(
                $importData['bot_replies'],
                $botFlowResult['botFlow']->_id,
                $vendorId

            );

            if (!$botRepliesResult['success']) {
                // If bot replies creation failed, we might want to keep the flow or delete it
                // For now, we'll keep the flow and report partial success
                return $this->engineResponse(1, [], __tr('Bot Flow imported successfully, but some bot replies could not be created'));
            }

            \Log::info('BotFlow import: reply ID mapping generated', [
                'vendor_id'   => $vendorId,
                'new_flow_id' => $botFlowResult['botFlow']->_id,
                'replyIdMap'  => $botRepliesResult['replyIdMap'] ?? null,
                'total_mapped'=> isset($botRepliesResult['replyIdMap']) ? count($botRepliesResult['replyIdMap']) : 0,
            ]);


            // sanitize the __data coloumn
            
            return $this->engineResponse(1, [], __tr('Bot Flow imported successfully'));

        } catch (\Exception $e) {
            return $this->engineResponse(2, [], __tr('Error importing bot flow'));
        }
    }

    /**
     * Validate import data structure
     *
     * @param array $importData
     * @return array
     */
    private function validateImportData($importData)
    {
        // Check required fields
        if (!isset($importData['bot_flow'])) {
            return ['valid' => false, 'message' => __tr('Invalid import file: missing bot_flow data')];
        }

        $botFlow = $importData['bot_flow'];
        $requiredFields = ['title', 'trigger_type'];

        foreach ($requiredFields as $field) {
            if (!isset($botFlow[$field]) || empty($botFlow[$field])) {
                return ['valid' => false, 'message' => __tr('Invalid import file: missing required field ') . $field];
            }
        }

        // Validate trigger type
        $validTriggerTypes = array_keys(configItem('bot_reply_trigger_types'));
        if (!in_array($botFlow['trigger_type'], $validTriggerTypes)) {
            return ['valid' => false, 'message' => __tr('Invalid trigger type in import file')];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Create bot flow from import data
     *
     * @param array $flowData
     * @param int $vendorId
     * @return array
     */
    private function createBotFlowFromImport($flowData, $vendorId)
    {
        try {
            $botFlowData = [
                'status'              => 2, // inactive by default
                'whatsapp_flow_id'    => $flowData['whatsapp_flow_id'],
                'whatsapp_sync_status'=> $flowData['whatsapp_sync_status'],
                'whatsapp_sync_at'    => $flowData['whatsapp_sync_at'],
                'whatsapp_meta_data'  => $flowData['whatsapp_meta_data'],
                'title'               => $flowData['title'],
                'trigger_type'        => $flowData['trigger_type'],
                'start_trigger'       => $flowData['start_trigger'],
                'vendors__id'         => $vendorId,
                '__data'              => $flowData['__data'],
            ];
        
            $botFlow = $this->botFlowRepository->storeIt($botFlowData);
        
            if ($botFlow) {
                $flowData = $botFlow['__data']; // full __data
        
                    $newUid = $botFlow->_uid;
    
                    // 1️⃣ Update operator body HTML context_flow_uid + data-post-data
                    if (isset($flowData['flow_builder_data']['operators'])) {
                        foreach ($flowData['flow_builder_data']['operators'] as &$operator) {
                            if (isset($operator['properties']['body'])) {
                                $bodyHtml = $operator['properties']['body'];
    
                                // Replace context_flow_uid in JSON attributes inside HTML
                                $bodyHtml = preg_replace(
                                    '/"context_flow_uid":\s*"[^"]+"/',
                                    '"context_flow_uid": "'.$newUid.'"',
                                    $bodyHtml
                                );
    
                         
    
                                $operator['properties']['body'] = $bodyHtml;

                            
                             }
                            
                        }
                    }
                    
                    if(isset($flowData['flow_nodes_data'])){
                        $olduid = $flowData['flow_nodes_data']['flow_id'];
                        $flowData['flow_nodes_data']['flow_id'] = $newUid;
                    }else{
                        \log::info('flow_nodes_data not found');
                    }
                  
    
                    $botFlow->__data = $flowData;
                    $botFlow->save();
                
        
                return ['success' => true, 'botFlow' => $botFlow];
            }
        
            return ['success' => false, 'message' => __tr('Failed to create bot flow')];
        
        } catch (\Exception $e) {
            return ['success' => false, 'message' => __tr('Error creating bot flow: ') . $e->getMessage()];
        }
    }
    
    
    

  /**
 * Create bot replies from import data
 *
 * @param array $botRepliesData
 * @param int $newFlowId
 * @param int $vendorId
 * @return array
 */
private function createBotRepliesFromImport($botRepliesData, $newFlowId, $vendorId)
{
    
       //fetch flow data
       $flowData = $this->botFlowRepository->fetchIt([
        'vendors__id' => $vendorId,
       ]);

    try {
        // Build a map of old reply id => new reply id
        $replyIdMap = [];
        foreach ($botRepliesData as $replyData) {
            $newReplyUid = (string) Str::uuid();

            $botReplyData = [
                'name'           => $replyData['name'] . ' - Imported',
                '_uid'           => $newReplyUid,
                'bot_flows__id'  => $newFlowId,
                'status'         => 2, // inactive by default
                'vendors__id'    => $vendorId,
                'reply_text'     => $replyData['reply_text'] ?? null,
                'trigger_type'   => $replyData['trigger_type'] ?? null,
                'reply_trigger'  => $replyData['reply_trigger'] ?? null,
                'priority_index' => $replyData['priority_index'] ?? null,
                'bot_replies__id'=> $replyData['bot_replies__id'] ?? null,
                '__data'         => $replyData['__data'] ?? ($replyData['_data'] ?? null),
            ];

          

            $botReply = $this->botReplyRepository->storeIt($botReplyData);

          

            if ($botReply && (isset($replyData['__data']) || isset($replyData['_data']))) {
                $botData = $replyData['__data'] ?? $replyData['_data'];

                // Clean up interactive elements (just like duplicate/clone logic)
                if (isset($botData['interaction_message']['buttons'])) {
                    $botData['interaction_message']['buttons'] = [];
                }
                if (isset($botData['interaction_message']['list_data'])) {
                    $botData['interaction_message']['list_data'] = [
                        'button_text' => $botData['interaction_message']['list_data']['button_text'] ?? '',
                    ];
                }

                // Replace context_flow_uid inside HTML if present
                if (isset($botData['interaction_message']['body'])) {
                    $botData['interaction_message']['body'] = preg_replace(
                        '/"context_flow_uid":\s*"[^"]+"/',
                        '"context_flow_uid": "'.$botReply->_uid.'"',
                        $botData['interaction_message']['body']
                    );
                }

                $botReply->__data = $botData;
                $botReply->save();

                // Normalize flow __data to array regardless of current type
                $flowRawData = $flowData->__data;
                if (is_array($flowRawData)) {
                    $dataArr = $flowRawData;
                } elseif (is_string($flowRawData)) {
                    $decoded = json_decode($flowRawData, true);
                    $dataArr = json_last_error() === JSON_ERROR_NONE ? ($decoded ?: []) : [];
                } else {
                    // Object or other type; convert via encode/decode
                    $dataArr = json_decode(json_encode($flowRawData), true) ?: [];
                }

                // Update flow_id to the current flow UID if present
                if (isset($dataArr['flow_nodes_data']['flow_id'])) {
                    $dataArr['flow_nodes_data']['flow_id'] = $flowData->_uid ?? $dataArr['flow_nodes_data']['flow_id'];
                }

                // Save back as structured data (array); model casts will handle persistence
                $flowData->__data = $dataArr;
                $flowData->save();
            }

            // Track mapping of old imported id to newly generated id
            if ($botReply && isset($replyData['id']) && !empty($replyData['id'])) {
                $replyIdMap[$replyData['id']] = $botReply->_id;
                \Log::info('BotFlow import: mapped imported reply ID to new ID', [
                    'vendor_id'        => $vendorId,
                    'new_flow_id'      => $newFlowId,
                    'old_imported_id'  => $replyData['id'],
                    'new_db_id'        => $botReply->_id,
                    'new_db_uid'       => $botReply->_uid,
                    'reply_name'       => $botReply->name,
                ]);
            }
        }

        // After creating all replies, remap any old linked IDs to new IDs
        $importedReplies = $this->botReplyRepository->fetchItAll([
            'bot_flows__id' => $newFlowId,
        ]);

        foreach ($importedReplies as $createdReply) {
            $oldLinkedId = $createdReply->bot_replies__id ?? null;
            // keep nulls as-is; remap only when mapping exists
            if (!empty($oldLinkedId) && isset($replyIdMap[$oldLinkedId])) {
                $createdReply->bot_replies__id = $replyIdMap[$oldLinkedId];
                $createdReply->save();
                \Log::info('BotFlow import: remapped bot_replies__id', [
                    'vendor_id'      => $vendorId,
                    'new_flow_id'    => $newFlowId,
                    'reply_db_id'    => $createdReply->_id,
                    'old_linked_id'  => $oldLinkedId,
                    'new_linked_id'  => $replyIdMap[$oldLinkedId],
                ]);
            }
        }

        \Log::info('BotFlow import: replyIdMap summary', [
            'vendor_id'   => $vendorId,
            'new_flow_id' => $newFlowId,
            'total_mapped'=> count($replyIdMap),
            'map'         => $replyIdMap,
        ]);

        // sanitize the __data coloumn
        $this->sanitizeImportedBotFlow__data($newFlowId);   

    

        return ['success' => true, 'replyIdMap' => $replyIdMap];

    } catch (\Exception $e) {
        return ['success' => false, 'message' => __tr('Error creating bot replies: ') . $e->getMessage()];
    }
}


private function sanitizeImportedBotFlow__data($newFlowId)
{
    $flowData = $this->botFlowRepository->fetchIt([
        '_id' => $newFlowId,
    ]);
  

    $replyData = $this->botReplyRepository->fetchItAll([
        'bot_flows__id' => $newFlowId,
    ]);

  
    $__data = $flowData->__data;

    // Step 1: Convert to array
    $dataArr = json_decode(json_encode($__data), true);

    // Step 2: Get operator keys ONCE before the loop
    $operatorKeys = array_keys($dataArr['flow_builder_data']['operators']);

    // Step 3: Replace operator keys & references
    foreach ($replyData as $index => $reply) {
        $newUid = $reply->_uid;
    
        if (isset($operatorKeys[$index])) {
            $oldUid = $operatorKeys[$index];
    
            // ✅ Replace operator key
            $dataArr['flow_builder_data']['operators'][$newUid] = $dataArr['flow_builder_data']['operators'][$oldUid];
            unset($dataArr['flow_builder_data']['operators'][$oldUid]);
    
            // ✅ Replace everywhere else (values only)
            array_walk_recursive($dataArr, function (&$value) use ($oldUid, $newUid) {
                if (is_string($value)) {
                    // Replace exact match
                    if ($value === $oldUid) {
                        $value = $newUid;
                    }
                    // Replace inside HTML or JSON strings
                    if (strpos($value, $oldUid) !== false) {
                        $value = str_replace($oldUid, $newUid, $value);
                    }
                }
            });
    
            \Log::info('Replaced UID', [
                'old_uid' => $oldUid,
                'new_uid' => $newUid,
                'index'   => $index,
            ]);
        }
    }
    

    // Step 4: Convert back to object and save
    $updatedData = json_decode(json_encode($dataArr));

    $this->botFlowRepository->updateIt($newFlowId, ['__data' => $updatedData]);


    return $updatedData;
}

    // private function sanitizeImportedBotFlow__data($vendorId)
    // {
    //     //fetch reply data
    //    $replyData = $this->botReplyRepository->fetchItAll([
    //     'vendors__id' => $vendorId,
    //    ]);

    //    foreach ($replyData as $reply) {
        
    //    }

    // }

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
        $uniqueValidationRules = [
           "title" => [
               Rule::unique('bot_flows')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId))->ignore($botFlow->_id, '_id')
           ]
        ];

        // Add start_trigger uniqueness validation only if trigger_type is not 'welcome' or 'new_message'
        if (!in_array($request->trigger_type, ['welcome', 'new_message'])) {
            $uniqueValidationRules['start_trigger'] = [
                Rule::unique('bot_flows')->where(fn (Builder $query) => $query->where('vendors__id', $vendorId))->ignore($botFlow->_id, '_id')
            ];
        }

        $request->validate($uniqueValidationRules);

        $updateData = [
            'title' => $request->title,
            'start_trigger' => $request->start_trigger,
            'trigger_type' => $request->trigger_type,
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

            // Get bot reply data for enhanced payload information
            $botReply = $this->botReplyRepository->fetchIt([
                '_uid' => $operatorId,
                'vendors__id' => getVendorId(),
            ]);

            $nodeType = $this->determineNodeType($operator, $operatorId, $botReply);
            $payload = $this->extractNodePayload($operator, $nextNodeMap[$operatorId] ?? [], $botReply);

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
                // Check for team_assignment_message data
                elseif (isset($botReply->__data['team_assignment_message'])) {
                    $nodeType = 'team_assignment';
                    $teamAssignmentData = $botReply->__data['team_assignment_message'];
                    $payload = array_merge($payload, [
                        'assignment_message' => $teamAssignmentData['assignment_message'] ?? '',
                        'assigned_team_member' => $teamAssignmentData['assigned_team_member'] ?? '',
                        'assigned_team_member_name' => $teamAssignmentData['assigned_team_member_name'] ?? '',
                        'assigned_team_member_email' => $teamAssignmentData['assigned_team_member_email'] ?? '',
                    ]);
                }
                // Check for media_message data
                elseif (isset($botReply->__data['media_message'])) {
                    $nodeType = 'media_message';
                    $mediaData = $botReply->__data['media_message'];
                    $payload = array_merge($payload, [
                        'media_type' => $mediaData['media_type'] ?? 'image',
                        'media_url' => $mediaData['media_url'] ?? '',
                        'caption' => $mediaData['caption'] ?? '',
                        'filename' => $mediaData['filename'] ?? ''
                    ]);
                }
                // Check for webhook_message data
                elseif (isset($botReply->__data['webhook_message'])) {
                    $nodeType = 'webhook';
                    $webhookData = $botReply->__data['webhook_message'];
                    $payload = array_merge($payload, [
                        'webhook_url' => $webhookData['webhook_url'] ?? '',
                        'http_method' => $webhookData['http_method'] ?? 'POST',
                        'request_body' => $webhookData['request_body'] ?? '{}',
                        'timeout' => $webhookData['timeout'] ?? 30,
                        'success_message' => $webhookData['success_message'] ?? 'Webhook executed successfully',
                        'error_message' => $webhookData['error_message'] ?? 'Webhook execution failed',
                        'response_mapping' => $webhookData['response_mapping'] ?? [],
                        // Set webhook-specific next node connections
                        'next_node' => $nextNodeMap[$operatorId]['success'] ?? null,
                        'failed_next_node' => $nextNodeMap[$operatorId]['delivery_failed'] ?? null,
                    ]);
                }
                // Check for wait_message data
                elseif (isset($botReply->__data['wait_message'])) {
                    $nodeType = 'wait';
                    $waitData = $botReply->__data['wait_message'];
                    $payload = array_merge($payload, [
                        'wait_time' => $waitData['wait_delay_seconds'] ?? 5,
                        'wait_message' => $waitData['wait_message'] ?? 'Please wait...',
                        'next_node' => $nextNodeMap[$operatorId]['simple_output'] ?? null,
                    ]);
                }
                // Check for whatsapp_template_message data
                elseif (isset($botReply->__data['whatsapp_template_message'])) {
                    $nodeType = 'whatsapp_template';
                    $templateData = $botReply->__data['whatsapp_template_message'];
                    $payload = array_merge($payload, [
                        'display_format' => $templateData['display_format'] ?? 'list',
                        'template_filter' => $templateData['template_filter'] ?? 'all',
                        'next_node' => $nextNodeMap[$operatorId]['simple_output'] ?? null,
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
     * Determine node type based on operator properties and bot reply data
     *
     * @param array $operator
     * @param string $operatorId
     * @param object|null $botReply
     * @return string
     */
    private function determineNodeType($operator, $operatorId, $botReply)
    {
        $properties = $operator['properties'] ?? [];
        $outputs = $properties['outputs'] ?? [];
        
        \Illuminate\Support\Facades\Log::info('Determining node type', [
            'node_id' => $operatorId,
            'has_bot_reply' => !__isEmpty($botReply),
            'outputs_count' => count($outputs),
            'output_keys' => array_keys($outputs),
            'properties_keys' => array_keys($properties)
        ]);
        
        // PRIORITY 1: Check bot reply data first (most reliable source)
        if (!__isEmpty($botReply)) {
            // Check for media_message data
            if (isset($botReply->__data['media_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: media_message', ['node_id' => $operatorId]);
                return 'media_message';
            }
            
            // Check for goto_message data
            if (isset($botReply->__data['goto_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: goto', ['node_id' => $operatorId]);
                return 'goto';
            }
            
            // Check for question_message data
            if (isset($botReply->__data['question_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: question', ['node_id' => $operatorId]);
                return 'question';
            }

            // Check for team_assignment_message data
            if (isset($botReply->__data['team_assignment_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: team_assignment', ['node_id' => $operatorId]);
                return 'team_assignment';
            }

            // Check for webhook_message data
            if (isset($botReply->__data['webhook_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: webhook', ['node_id' => $operatorId]);
                return 'webhook';
            }

            // Check for custom_field_message data
            if (isset($botReply->__data['custom_field_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: custom_field', ['node_id' => $operatorId]);
                return 'custom_field';
            }

            // Check for wait_message data
            if (isset($botReply->__data['wait_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: wait', ['node_id' => $operatorId]);
                return 'wait';
            }

            // Check for whatsapp_template_message data
            if (isset($botReply->__data['whatsapp_template_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: whatsapp_template', ['node_id' => $operatorId]);
                return 'whatsapp_template';
            }

            // Check for stay_in_session_message data
            if (isset($botReply->__data['stay_in_session_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: stay_in_session', ['node_id' => $operatorId]);
                return 'stay_in_session';
            }

            // Check for flow_message data
            if (isset($botReply->__data['flow_message'])) {
                \Illuminate\Support\Facades\Log::info('Determined node type: flow', ['node_id' => $operatorId]);
                return 'flow';
            }

            // Check for interaction_message data (buttons/lists)
            if (isset($botReply->__data['interaction_message'])) {
                $interactionData = $botReply->__data['interaction_message'];
                
                // Check for list data first
                if (!empty($interactionData['list_data']['sections'])) {
                    \Illuminate\Support\Facades\Log::info('Determined node type: interactive (list from bot_reply)', ['node_id' => $operatorId]);
                    return 'interactive';
                }
                
                // Check for buttons - only if they are actual interactive buttons, not empty arrays
                if (!empty($interactionData['buttons']) && is_array($interactionData['buttons'])) {
                    // Filter out empty button values
                    $validButtons = array_filter($interactionData['buttons'], function($button) {
                        return !empty(trim($button));
                    });
                    
                    if (!empty($validButtons)) {
                        \Illuminate\Support\Facades\Log::info('Determined node type: interactive (buttons from bot_reply)', [
                            'node_id' => $operatorId,
                            'button_count' => count($validButtons)
                        ]);
                        return 'interactive';
                    }
                }
                
                // If interaction_message exists but has no valid buttons or lists, it's likely a simple message
                \Illuminate\Support\Facades\Log::info('Found interaction_message but no valid buttons/lists - treating as message', [
                    'node_id' => $operatorId,
                    'has_buttons' => isset($interactionData['buttons']),
                    'buttons_count' => count($interactionData['buttons'] ?? []),
                    'has_list_sections' => isset($interactionData['list_data']['sections'])
                ]);
            }
            
            // If bot reply exists but has no special message types, it's a simple message
            \Illuminate\Support\Facades\Log::info('Bot reply exists but no special message types found - treating as message', ['node_id' => $operatorId]);
            return 'message';
        }
        
        // PRIORITY 2: Check for list_data in properties (this indicates a list type interactive)
        if (isset($properties['list_data.sections']) && !empty($properties['list_data.sections'])) {
            \Illuminate\Support\Facades\Log::info('Determined node type: interactive (list_data.sections)', ['node_id' => $operatorId]);
            return 'interactive';  // Will be handled as list type in payload
        }
        
        // PRIORITY 3: Check for list_data in properties (alternative format)
        if (isset($properties['list_data']['sections']) && !empty($properties['list_data']['sections'])) {
            \Illuminate\Support\Facades\Log::info('Determined node type: interactive (list_data sections)', ['node_id' => $operatorId]);
            return 'interactive';  // Will be handled as list type in payload
        }

        // PRIORITY 4: Check for list data in the flow structure format
        // Look for outputs that match list pattern: sections___section_*___rows___row_*___title
        $hasListOutputs = false;
        foreach ($outputs as $outputKey => $output) {
            if (strpos($outputKey, 'sections___section_') !== false && 
                strpos($outputKey, '___rows___row_') !== false && 
                strpos($outputKey, '___title') !== false) {
                $hasListOutputs = true;
                break;
            }
        }
        
        if ($hasListOutputs) {
            \Illuminate\Support\Facades\Log::info('Determined node type: interactive (list from output patterns)', [
                'node_id' => $operatorId,
                'output_keys' => array_keys($outputs)
            ]);
            return 'interactive';  // Will be handled as list type in payload
        }

        // PRIORITY 5: Filter out UI/system outputs that are not actual flow connections
        $actualFlowOutputs = [];
        $uiOutputKeys = ['no_input', 'no_match', 'delivery_failed', 'simple_output']; // Standard WhatsApp flow outputs
        
        foreach ($outputs as $outputKey => $output) {
            // Skip standard WhatsApp flow outputs
            if (in_array($outputKey, $uiOutputKeys)) {
                continue;
            }
            
            // Skip outputs that look like UI elements (contain HTML or specific patterns)
            $label = $output['label'] ?? '';
            if (strpos($label, '<') !== false || strpos($label, 'fa-') !== false || strpos($label, 'btn') !== false) {
                \Illuminate\Support\Facades\Log::info('Skipping UI output', [
                    'node_id' => $operatorId,
                    'output_key' => $outputKey,
                    'label' => $label
                ]);
                continue;
            }
            
            // Skip simple continuation outputs that are not meaningful interactions
            $labelLower = strtolower(trim($label));
            if (in_array($labelLower, ['continue', 'next', 'ok', 'proceed', '']) || empty($labelLower)) {
                \Illuminate\Support\Facades\Log::info('Skipping simple continuation output', [
                    'node_id' => $operatorId,
                    'output_key' => $outputKey,
                    'label' => $label
                ]);
                continue;
            }
            
            $actualFlowOutputs[$outputKey] = $output;
        }

        \Illuminate\Support\Facades\Log::info('Filtered outputs', [
            'node_id' => $operatorId,
            'original_count' => count($outputs),
            'filtered_count' => count($actualFlowOutputs),
            'filtered_keys' => array_keys($actualFlowOutputs)
        ]);

        // PRIORITY 6: Check for goto node based on filtered outputs
        if (count($actualFlowOutputs) === 1) {
            $outputKeys = array_keys($actualFlowOutputs);
            $firstOutput = $actualFlowOutputs[$outputKeys[0]];
            $label = strtolower($firstOutput['label'] ?? '');

            if ($label === 'redirect' || $label === 'goto') {
                \Illuminate\Support\Facades\Log::info('Determined node type: goto', ['node_id' => $operatorId]);
                return 'goto';
            }
        }

        // PRIORITY 7: Check for actual interactive elements (multiple meaningful outputs)
        if (count($actualFlowOutputs) > 1) {
            \Illuminate\Support\Facades\Log::info('Determined node type: interactive (multiple meaningful outputs)', [
                'node_id' => $operatorId,
                'interactive_count' => count($actualFlowOutputs)
            ]);
            return 'interactive';
        }

        // PRIORITY 8: Check for single meaningful interactive button
        if (count($actualFlowOutputs) === 1) {
            \Illuminate\Support\Facades\Log::info('Determined node type: interactive (single meaningful button)', [
                'node_id' => $operatorId,
                'output_key' => array_keys($actualFlowOutputs)[0],
                'label' => $actualFlowOutputs[array_keys($actualFlowOutputs)[0]]['label'] ?? ''
            ]);
            return 'interactive';
        }

        // DEFAULT: No meaningful outputs means it's a simple message node
        \Illuminate\Support\Facades\Log::info('Determined node type: message (default - no meaningful outputs)', ['node_id' => $operatorId]);
        return 'message';
    }


    /**
     * Extract node payload from operator properties
     *
     * @param array $operator
     * @param array $nextNodes
     * @param object|null $botReply
     * @return array
     */
    private function extractNodePayload($operator, $nextNodes, $botReply = null)
    {
        $properties = $operator['properties'] ?? [];
        $outputs = $properties['outputs'] ?? [];
        
        // Extract the actual reply text from bot reply data or parse from HTML
        $replyText = $this->extractReplyText($operator, $botReply);
        
        $payload = [
            'text' => $replyText
        ];

            // PRIORITY 1: Check bot reply data first for accurate node type determination
        if (!__isEmpty($botReply)) {
            // If bot reply has interaction_message with valid buttons or lists, handle as interactive
            if (isset($botReply->__data['interaction_message'])) {
                $interactionData = $botReply->__data['interaction_message'];
                
                // Check for list data first
                if (!empty($interactionData['list_data']['sections'])) {
                    $payload['interactive_type'] = 'list';
                    
                    // Process the list data and map next_nodes from the flow structure
                    $listData = $interactionData['list_data'];
                    $processedSections = [];
                    
                    foreach ($listData['sections'] as $section) {
                        $processedSection = [
                            'title' => $section['title'] ?? '',
                            'rows' => []
                        ];
                        
                        foreach ($section['rows'] ?? [] as $row) {
                            $rowId = $row['row_id'] ?? $row['id'] ?? '';
                            $rowTitle = $row['title'] ?? '';
                            $nextNode = null;
                            
                            // Try to find the next node mapping from the flow structure
                            // Method 1: Try exact row ID match first
                            foreach ($nextNodes as $connectorKey => $nodeId) {
                                if (strpos($connectorKey, 'sections___section_') !== false && 
                                    strpos($connectorKey, '___rows___row_' . $rowId . '___title') !== false) {
                                    $nextNode = $nodeId;
                                    break;
                                }
                            }
                            
                            // Method 2: If no exact match, try to match by title
                            if ($nextNode === null && !empty($rowTitle)) {
                                foreach ($nextNodes as $connectorKey => $nodeId) {
                                    if (strpos($connectorKey, 'sections___section_') !== false && 
                                        strpos($connectorKey, '___title') !== false) {
                                        // Extract the output label from the flow structure to compare with title
                                        // We need to check if this connector corresponds to our row title
                                        // This is a fallback method when row IDs don't match
                                        $outputKey = $connectorKey;
                                        // Check if we can find this output in the operator outputs
                                        $operatorOutputs = $properties['outputs'] ?? [];
                                        if (isset($operatorOutputs[$outputKey]) && 
                                            strtolower(trim($operatorOutputs[$outputKey]['label'] ?? '')) === strtolower(trim($rowTitle))) {
                                            $nextNode = $nodeId;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            // Method 3: If still no match, try to find by any partial row ID match
                            if ($nextNode === null) {
                                foreach ($nextNodes as $connectorKey => $nodeId) {
                                    if (strpos($connectorKey, 'sections___section_') !== false && 
                                        strpos($connectorKey, '___rows___') !== false && 
                                        strpos($connectorKey, '___title') !== false) {
                                        // Extract the actual row ID from the connector key
                                        preg_match('/___rows___row_([^_]+)___title/', $connectorKey, $matches);
                                        if (!empty($matches[1])) {
                                            $connectorRowId = $matches[1];
                                            // Check if this row ID contains our row ID or vice versa
                                            if (strpos($connectorRowId, $rowId) !== false || strpos($rowId, $connectorRowId) !== false) {
                                                $nextNode = $nodeId;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            $processedSection['rows'][] = [
                                'id' => $rowId,
                                'row_id' => $row['row_id'] ?? $row['id'] ?? '',
                                'title' => $rowTitle,
                                'description' => $row['description'] ?? '',
                                'next_node' => $nextNode
                            ];
                            
                            // Debug logging for list row mapping from bot reply
                            \Illuminate\Support\Facades\Log::info('List row mapping from bot reply debug', [
                                'row_id' => $rowId,
                                'row_title' => $rowTitle,
                                'next_node' => $nextNode,
                                'available_connector_keys' => array_keys($nextNodes),
                                'matched_connector' => array_filter(array_keys($nextNodes), function($key) use ($rowId, $rowTitle) {
                                    return strpos($key, '___rows___row_' . $rowId . '___title') !== false ||
                                           (strpos($key, '___title') !== false && !empty($rowTitle));
                                })
                            ]);
                        }
                        
                        $processedSections[] = $processedSection;
                    }
                    
                    $payload['list_data'] = [
                        'button_text' => $listData['button_text'] ?? 'Select an option',
                        'sections' => $processedSections
                    ];
                    
                    \Illuminate\Support\Facades\Log::info('Processed list data from bot reply', [
                        'node_id' => $botReply->_uid ?? 'unknown',
                        'sections_count' => count($processedSections),
                        'total_rows' => array_sum(array_map(function($section) { 
                            return count($section['rows']); 
                        }, $processedSections)),
                        'next_nodes_available' => array_keys($nextNodes)
                    ]);
                    
                    return $payload;
                }

                // Check for flow message data
                if (isset($botReply->__data['flow_message'])) {
                    $flowMessage = $botReply->__data['flow_message'];

                    return [
                        'text' => $replyText,
                        'whatsapp_flow_id' => $flowMessage['whatsapp_flow_id'] ?? null,
                        'flow_name' => $flowMessage['flow_name'] ?? '',
                        'flow_status' => $flowMessage['flow_status'] ?? '',
                        'flow_categories' => $flowMessage['flow_categories'] ?? [],
                        'header_text' => $flowMessage['header_text'] ?? '',
                        'body_text' => $flowMessage['body_text'] ?? '',
                        'footer_text' => $flowMessage['footer_text'] ?? '',
                        'next_node' => $nextNodes['next'] ?? $nextNodes['simple_output'] ?? null,
                        'failed_next_node' => $nextNodes['delivery_failed'] ?? null,
                    ];
                }
                
                // Check for valid buttons
                if (!empty($interactionData['buttons']) && is_array($interactionData['buttons'])) {
                    $validButtons = array_filter($interactionData['buttons'], function($button) {
                        return !empty(trim($button));
                    });
                    
                    if (!empty($validButtons)) {
                        $payload['interactive_type'] = 'button';
                        $buttons = [];
                        
                        // Create buttons with correct mapping based on button order and flow connections
                        // Reset the array to ensure we start from index 0
                        $validButtons = array_values($validButtons);
                        
                        foreach ($validButtons as $index => $buttonTitle) {
                            $buttonId = ($index + 1); // Use numeric button IDs like "1", "2", "3"
                            
                            // The key in nextNodes should match the button number from the flow structure
                            // Based on the flow_builder_data, buttons are numbered as "1", "2", "3", etc.
                            $nextNode = $nextNodes[(string)$buttonId] ?? null;
                            
                            // If string key doesn't work, try numeric key
                            if ($nextNode === null) {
                                $nextNode = $nextNodes[$buttonId] ?? null;
                            }
                            
                            // Try additional possible key formats for button mapping if still not found
                            if ($nextNode === null) {
                                $possibleKeys = [
                                    'button_' . $buttonId,       // "button_1", "button_2", "button_3"
                                    'btn_' . $buttonId,          // "btn_1", "btn_2", "btn_3"
                                    $index,                      // 0, 1, 2 (array index)
                                    (string)$index               // "0", "1", "2"
                                ];
                                
                                // Try to find the next node using different key formats
                                foreach ($possibleKeys as $key) {
                                    if (isset($nextNodes[$key])) {
                                        $nextNode = $nextNodes[$key];
                                        break;
                                    }
                                }
                            }
                            
                            $buttons[] = [
                                'id' => 'button_' . $buttonId,
                                'title' => $buttonTitle,
                                'next_node' => $nextNode
                            ];
                            
                            // Debug logging to track button mapping
                            \Illuminate\Support\Facades\Log::info('Button mapping debug', [
                                'button_index' => $index,
                                'button_id' => $buttonId,
                                'button_title' => $buttonTitle,
                                'next_node' => $nextNode,
                                'available_keys' => array_keys($nextNodes),
                                'tried_key' => (string)$buttonId,
                                'valid_buttons_count' => count($validButtons)
                            ]);
                        }
                        $payload['buttons'] = $buttons;
                        
                        \Illuminate\Support\Facades\Log::info('Created interactive buttons from bot reply data', [
                            'node_id' => $botReply->_uid ?? 'unknown',
                            'buttons_count' => count($buttons),
                            'buttons' => $buttons,
                            'next_nodes_available' => $nextNodes,
                            'next_nodes_keys' => array_keys($nextNodes)
                        ]);
                        
                        return $payload;
                    }
                }
            }

            // Check for stay_in_session_message data
            if (isset($botReply->__data['stay_in_session_message'])) {
                // Stay in session nodes don't have next nodes - they prevent flow termination
                $stayInSessionData = $botReply->__data['stay_in_session_message'];
                $payload['text'] = $stayInSessionData['session_message'] ?? $payload['text'] ?? 'Session will remain active...';
                // No next_node for stay in session - this prevents flow termination
                return $payload;
            }

            // If bot reply exists but has no valid interactive elements, it's a simple message
            $payload['next_node'] = !empty($nextNodes) ? reset($nextNodes) : null;
            return $payload;
        }

        // PRIORITY 2: Try to extract list data from properties
        $listData = $this->extractListData($properties, $outputs, $nextNodes);
        if ($listData) {
            return array_merge($payload, $listData);
        }

        // PRIORITY 3: Check for list data in properties or flow structure
        $hasListStructure = false;
        foreach ($outputs as $outputKey => $output) {
            if (strpos($outputKey, 'sections___section_') !== false && 
                strpos($outputKey, '___rows___row_') !== false && 
                strpos($outputKey, '___title') !== false) {
                $hasListStructure = true;
                break;
            }
        }

        if ($hasListStructure || (isset($properties['list_data.sections']) && !empty($properties['list_data.sections']))) {
            $payload['interactive_type'] = 'list';
            
            // Check if we have list_data.sections in properties (new format)
            if (isset($properties['list_data.sections']) && !empty($properties['list_data.sections'])) {
                $payload['list_data'] = [
                    'button_text' => 'Select an option',
                    'sections' => []
                ];
                
                foreach ($properties['list_data.sections'] as $section) {
                    $processedSection = [
                        'title' => $section['title'] ?? '',
                        'rows' => []
                    ];
                    
                    foreach ($section['rows'] ?? [] as $row) {
                        $rowId = $row['row_id'] ?? $row['id'] ?? '';
                        $nextNode = null;
                        
                        // For the new format, try to find the connector key that matches this row
                        foreach ($nextNodes as $connectorKey => $nodeId) {
                            if (strpos($connectorKey, $rowId) !== false && strpos($connectorKey, 'title') !== false) {
                                $nextNode = $nodeId;
                                break;
                            }
                        }
                        
                        $processedSection['rows'][] = [
                            'id' => $rowId,
                            'row_id' => $rowId, // Ensure backward compatibility
                            'title' => $row['title'] ?? '',
                            'description' => $row['description'] ?? '',
                            'next_node' => $nextNode
                        ];
                        
                        // Debug logging for list row mapping
                        \Illuminate\Support\Facades\Log::info('List row mapping debug', [
                            'row_id' => $rowId,
                            'row_title' => $row['title'] ?? '',
                            'next_node' => $nextNode,
                            'available_connector_keys' => array_keys($nextNodes),
                            'matched_connector' => array_filter(array_keys($nextNodes), function($key) use ($rowId) {
                                return strpos($key, $rowId) !== false && strpos($key, 'title') !== false;
                            })
                        ]);
                    }
                    
                    $payload['list_data']['sections'][] = $processedSection;
                }
            }
            // Check if we have the old format list_data['sections']
            elseif (isset($properties['list_data']['sections']) && !empty($properties['list_data']['sections'])) {
                $payload['list_data'] = [
                    'button_text' => $properties['list_data']['button_text'] ?? 'Select an option',
                    'sections' => []
                ];
                
                foreach ($properties['list_data']['sections'] as $section) {
                    $processedSection = [
                        'title' => $section['title'] ?? '',
                        'rows' => []
                    ];
                    
                    foreach ($section['rows'] ?? [] as $row) {
                        $rowId = $row['row_id'] ?? $row['id'] ?? '';
                        $nextNode = null;
                        
                        // For the old format, try to find the connector key that matches this row
                        foreach ($nextNodes as $connectorKey => $nodeId) {
                            if (strpos($connectorKey, $rowId) !== false && strpos($connectorKey, 'title') !== false) {
                                $nextNode = $nodeId;
                                break;
                            }
                        }
                        
                        $processedSection['rows'][] = [
                            'id' => $rowId,
                            'row_id' => $rowId, // Ensure backward compatibility
                            'title' => $row['title'] ?? '',
                            'description' => $row['description'] ?? '',
                            'next_node' => $nextNode
                        ];
                    }
                    
                    $payload['list_data']['sections'][] = $processedSection;
                }
            }
            
            // Ensure we don't convert this to buttons later
            $payload['is_list'] = true;
            
            \Illuminate\Support\Facades\Log::info('Converted list-type interactive node', [
                'node_id' => $operator['properties']['title'] ?? 'unknown',
                'sections_count' => count($payload['list_data']['sections'] ?? []),
                'total_rows' => array_sum(array_map(function($section) { 
                    return count($section['rows']); 
                }, $payload['list_data']['sections'] ?? [])),
                'next_nodes_available' => array_keys($nextNodes),
                'has_list_data_sections' => isset($properties['list_data.sections']),
                'has_old_list_data' => isset($properties['list_data']['sections'])
            ]);
            
            return $payload;
        }

        // PRIORITY 4: Check for list data from flow structure outputs
        $hasListOutputs = false;
        foreach ($outputs as $outputKey => $output) {
            if (strpos($outputKey, 'sections___section_') !== false && 
                strpos($outputKey, '___rows___row_') !== false && 
                strpos($outputKey, '___title') !== false) {
                $hasListOutputs = true;
                break;
            }
        }
        
        if ($hasListOutputs) {
            $payload['interactive_type'] = 'list';
            $payload['is_list'] = true;
            
            // Extract list data from the flow structure
            $sections = [];
            $currentSection = null;
            $sectionId = null;
            
            // First, collect all list-related outputs and group them by section and row
            $listOutputs = [];
            foreach ($outputs as $outputKey => $output) {
                if (strpos($outputKey, 'sections___section_') !== false && 
                    strpos($outputKey, '___rows___row_') !== false && 
                    strpos($outputKey, '___title') !== false) {
                    
                    // Extract section and row IDs from the key
                    preg_match('/sections___section_([^_]+)___rows___row_([^_]+)___title/', $outputKey, $matches);
                    if (count($matches) >= 3) {
                        $extractedSectionId = $matches[1];
                        $rowId = $matches[2];
                        
                        if (!isset($listOutputs[$extractedSectionId])) {
                            $listOutputs[$extractedSectionId] = [];
                        }
                        
                        $listOutputs[$extractedSectionId][$rowId] = [
                            'output_key' => $outputKey,
                            'title' => $output['label'] ?? '',
                            'next_node' => $nextNodes[$outputKey] ?? null
                        ];
                    }
                }
            }
            
            // Now build the sections structure
            foreach ($listOutputs as $sectionId => $rows) {
                $section = [
                    'title' => $properties['section_title_section_' . $sectionId] ?? 'Section',
                    'rows' => []
                ];
                
                foreach ($rows as $rowId => $rowData) {
                    $section['rows'][] = [
                        'id' => $rowId,
                        'row_id' => $rowId, // Ensure backward compatibility
                        'title' => $rowData['title'],
                        'description' => $properties['row_description_section_' . $sectionId . '_row_' . $rowId] ?? '',
                        'next_node' => $rowData['next_node']
                    ];
                    
                    // Debug logging for list row mapping from flow structure
                    \Illuminate\Support\Facades\Log::info('List row mapping from flow structure debug', [
                        'output_key' => $rowData['output_key'],
                        'section_id' => $sectionId,
                        'row_id' => $rowId,
                        'row_title' => $rowData['title'],
                        'next_node' => $rowData['next_node'],
                        'available_next_nodes' => array_keys($nextNodes)
                    ]);
                }
                
                $sections[] = $section;
            }
            
            $payload['list_data'] = [
                'button_text' => 'Select an option',
                'sections' => $sections
            ];
            
            \Illuminate\Support\Facades\Log::info('Converted list-type interactive node from flow structure', [
                'node_id' => $operator['properties']['title'] ?? 'unknown',
                'sections_count' => count($sections),
                'total_rows' => array_sum(array_map(function($section) { 
                    return count($section['rows']); 
                }, $sections)),
                'next_nodes_available' => array_keys($nextNodes),
                'final_sections' => $sections
            ]);
            
            return $payload;
        }

        // PRIORITY 5: Check if it's a goto node
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

        // PRIORITY 6: Filter out system outputs and check for meaningful interactive outputs
        $actualFlowOutputs = [];
        $uiOutputKeys = ['no_input', 'no_match', 'delivery_failed', 'simple_output']; // Standard WhatsApp flow outputs
        
        foreach ($outputs as $outputKey => $output) {
            // Skip standard WhatsApp flow outputs
            if (in_array($outputKey, $uiOutputKeys)) {
                continue;
            }
            
            // Skip outputs that look like UI elements (contain HTML or specific patterns)
            $label = $output['label'] ?? '';
            if (strpos($label, '<') !== false || strpos($label, 'fa-') !== false || strpos($label, 'btn') !== false) {
                continue;
            }
            
            // Skip simple continuation outputs that are not meaningful interactions
            $labelLower = strtolower(trim($label));
            if (in_array($labelLower, ['continue', 'next', 'ok', 'proceed', '']) || empty($labelLower)) {
                continue;
            }
            
            $actualFlowOutputs[$outputKey] = $output;
        }

        // PRIORITY 7: Handle button type interactive nodes only if there are meaningful outputs
        if (!isset($payload['is_list']) && !empty($actualFlowOutputs)) {
            $payload['interactive_type'] = 'button';
            $buttons = [];
            foreach ($actualFlowOutputs as $outputId => $output) {
                $buttons[] = [
                    'id' => $outputId,
                    'title' => $output['label'] ?? '',
                    'next_node' => $nextNodes[$outputId] ?? null
                ];
            }

            if (!empty($buttons)) {
                $payload['buttons'] = $buttons;
                \Illuminate\Support\Facades\Log::info('Converted button-type interactive node', [
                    'node_id' => $properties['title'] ?? 'unknown',
                    'buttons_count' => count($buttons)
                ]);
                return $payload;
            }
        }

        // DEFAULT: Simple message node or webhook node
        $payload['next_node'] = !empty($nextNodes) ? reset($nextNodes) : null;
        
        // For webhook nodes, also check for success-specific connections
        if (isset($botReply) && !__isEmpty($botReply) && isset($botReply->__data['webhook_message'])) {
            // For webhook nodes, the main connection should be the success path
            // Look for 'success' connector specifically, fallback to first available
            $successNextNode = $nextNodes['success'] ?? $payload['next_node'];
            $payload['next_node'] = $successNextNode;
            
            // Also set failed_next_node if available
            $payload['failed_next_node'] = $nextNodes['delivery_failed'] ?? null;
            
            \Illuminate\Support\Facades\Log::info('Created webhook node payload', [
                'node_id' => $properties['title'] ?? 'unknown',
                'success_next_node' => $payload['next_node'],
                'failed_next_node' => $payload['failed_next_node'],
                'next_nodes_available' => array_keys($nextNodes)
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('Created simple message node payload', [
                'node_id' => $properties['title'] ?? 'unknown',
                'has_next_node' => !empty($payload['next_node']),
                'next_nodes_available' => array_keys($nextNodes)
            ]);
        }

        return $payload;
    }

    /**
     * Extract the actual reply text from bot reply data or parse from HTML
     *
     * @param array $operator
     * @param object|null $botReply
     * @return string
     */
    private function extractReplyText($operator, $botReply = null)
    {
        // PRIORITY 1: Get text from bot reply data (most reliable)
        if (!__isEmpty($botReply)) {
            // For simple message nodes, use reply_text field directly
            if (!empty($botReply->reply_text) && trim($botReply->reply_text) !== '') {
                \Illuminate\Support\Facades\Log::info('Extracted reply text from bot reply record', [
                    'node_id' => $botReply->_uid ?? 'unknown',
                    'reply_text' => $botReply->reply_text
                ]);
                return trim($botReply->reply_text);
            }
            
            // For interactive messages, use body_text from interaction_message data
            if (isset($botReply->__data['interaction_message']['body_text']) && 
                !empty(trim($botReply->__data['interaction_message']['body_text']))) {
                \Illuminate\Support\Facades\Log::info('Extracted reply text from interaction_message body_text', [
                    'node_id' => $botReply->_uid ?? 'unknown',
                    'body_text' => $botReply->__data['interaction_message']['body_text']
                ]);
                return trim($botReply->__data['interaction_message']['body_text']);
            }
            
            // For question messages, use the reply_text field
            if (isset($botReply->__data['question_message']) && !empty($botReply->reply_text)) {
                \Illuminate\Support\Facades\Log::info('Extracted reply text from question message', [
                    'node_id' => $botReply->_uid ?? 'unknown',
                    'reply_text' => $botReply->reply_text
                ]);
                return trim($botReply->reply_text);
            }
            
            // For other message types with specific text fields
            if (isset($botReply->__data['wait_message']['wait_message']) && 
                !empty(trim($botReply->__data['wait_message']['wait_message']))) {
                return trim($botReply->__data['wait_message']['wait_message']);
            }
            
            if (isset($botReply->__data['team_assignment_message']['assignment_message']) && 
                !empty(trim($botReply->__data['team_assignment_message']['assignment_message']))) {
                return trim($botReply->__data['team_assignment_message']['assignment_message']);
            }
            
            if (isset($botReply->__data['custom_field_message']['question_text']) && 
                !empty(trim($botReply->__data['custom_field_message']['question_text']))) {
                return trim($botReply->__data['custom_field_message']['question_text']);
            }
            
            if (isset($botReply->__data['webhook_message']['success_message']) &&
                !empty(trim($botReply->__data['webhook_message']['success_message']))) {
                \Illuminate\Support\Facades\Log::info('Extracted reply text from webhook success_message', [
                    'node_id' => $botReply->_uid ?? 'unknown',
                    'success_message' => $botReply->__data['webhook_message']['success_message']
                ]);
                return trim($botReply->__data['webhook_message']['success_message']);
            }

            if (isset($botReply->__data['stay_in_session_message']['session_message']) &&
                !empty(trim($botReply->__data['stay_in_session_message']['session_message']))) {
                return trim($botReply->__data['stay_in_session_message']['session_message']);
            }
            
            // Log when bot reply exists but no text found
            \Illuminate\Support\Facades\Log::warning('Bot reply exists but no text found', [
                'node_id' => $botReply->_uid ?? 'unknown',
                'reply_text' => $botReply->reply_text ?? 'null',
                'has_interaction_data' => isset($botReply->__data['interaction_message']),
                'bot_data_keys' => array_keys($botReply->__data ?? [])
            ]);
        }
        
        // PRIORITY 2: Extract from HTML body content
        $properties = $operator['properties'] ?? [];
        if (isset($properties['body'])) {
            // Parse HTML to extract the actual message text
            $bodyHtml = $properties['body'];
            
            // Look for the message content in the specific div structure
            if (preg_match('/<div class="lw-node-message">(.*?)<\/div>/', $bodyHtml, $matches)) {
                $extractedText = trim(strip_tags($matches[1]));
                if (!empty($extractedText)) {
                    \Illuminate\Support\Facades\Log::info('Extracted reply text from HTML body', [
                        'extracted_text' => $extractedText
                    ]);
                    return $extractedText;
                }
            }
            
            // Fallback: strip all HTML tags
            $cleanText = strip_tags($bodyHtml);
            if (!empty(trim($cleanText))) {
                return trim($cleanText);
            }
        }
        
        // PRIORITY 3: Extract from title (fallback, but clean HTML)
        if (isset($properties['title'])) {
            $titleHtml = $properties['title'];
            
            // Look for simple node text in span
            if (preg_match('/<span>([^<]+)<\/span>/', $titleHtml, $matches)) {
                $nodeText = trim($matches[1]);
                // Skip generic text like "simple node"
                if (!in_array(strtolower($nodeText), ['simple node', 'node', 'message'])) {
                    return $nodeText;
                }
            }
            
            // Fallback: strip HTML and return clean text
            $cleanTitle = strip_tags($titleHtml);
            if (!empty(trim($cleanTitle))) {
                return trim($cleanTitle);
            }
        }
        
        // DEFAULT: Return a placeholder
        \Illuminate\Support\Facades\Log::warning('Using fallback placeholder text', [
            'operator_id' => $operator['properties']['title'] ?? 'unknown'
        ]);
        return 'Please provide your information...';
    }

    /**
     * Extract list data safely from properties and outputs
     *
     * @param array $properties
     * @param array $outputs
     * @param array $nextNodes
     * @return array|null
     */
    private function extractListData($properties, $outputs, $nextNodes)
    {
        // Check if list_data exists in properties
        if (isset($properties['list_data']) && isset($properties['list_data']['sections']) && !empty($properties['list_data']['sections'])) {
            $payload = [
                'type' => 'interactive',
                'interactive_type' => 'list',
                'is_list' => true,
                'list_data' => [
                    'button_text' => $properties['list_data']['button_text'] ?? 'Select an option',
                    'sections' => []
                ]
            ];
            
            foreach ($properties['list_data']['sections'] as $section) {
                $processedSection = [
                    'title' => $section['title'] ?? '',
                    'rows' => []
                ];
                
                foreach ($section['rows'] ?? [] as $row) {
                    $rowId = $row['row_id'] ?? '';
                    $nextNode = null;
                    
                    // Create the exact connector pattern that matches the outputs
                    $connectorKey = "sections___section_zLdz9Z75FvKOcNe3___rows___" . $rowId . "___title";
                    
                    // Try to find exact match first
                    if (isset($nextNodes[$connectorKey])) {
                        $nextNode = $nextNodes[$connectorKey];
                    } else {
                        // Fallback: search for partial match
                        foreach ($nextNodes as $key => $nodeId) {
                            if (strpos($key, $rowId) !== false && strpos($key, 'title') !== false) {
                                $nextNode = $nodeId;
                                break;
                            }
                        }
                    }
                    
                    $processedSection['rows'][] = [
                        'id' => $rowId,
                        'row_id' => $rowId, // Ensure backward compatibility
                        'title' => $row['title'] ?? '',
                        'description' => $row['description'] ?? '',
                        'next_node' => $nextNode
                    ];
                }
                
                $payload['list_data']['sections'][] = $processedSection;
            }
            
            \Illuminate\Support\Facades\Log::info('Extracted list data from properties', [
                'sections_count' => count($payload['list_data']['sections']),
                'total_rows' => array_sum(array_map(function($section) { 
                    return count($section['rows']); 
                }, $payload['list_data']['sections']))
            ]);
            
            return $payload;
        }
        
        // Check for list structure in outputs
        $hasListOutputs = false;
        foreach ($outputs as $outputKey => $output) {
            if (strpos($outputKey, 'sections___section_') !== false && 
                strpos($outputKey, '___rows___row_') !== false && 
                strpos($outputKey, '___title') !== false) {
                $hasListOutputs = true;
                break;
            }
        }
        
        if ($hasListOutputs) {
            $payload = [
                'type' => 'interactive',
                'interactive_type' => 'list',
                'is_list' => true
            ];
            
            // Extract list data from the flow structure
            $sections = [];
            $currentSection = null;
            $sectionId = null;
            
            foreach ($outputs as $outputKey => $output) {
                if (strpos($outputKey, 'sections___section_') !== false && 
                    strpos($outputKey, '___rows___row_') !== false && 
                    strpos($outputKey, '___title') !== false) {
                    
                    // Extract section and row IDs from the key
                    preg_match('/sections___section_([^_]+)___rows___row_([^_]+)___title/', $outputKey, $matches);
                    if (count($matches) >= 3) {
                        $extractedSectionId = $matches[1];
                        $rowId = $matches[2];
                        
                        // If this is a new section, save the previous one and start a new one
                        if ($sectionId !== $extractedSectionId) {
                            if ($currentSection !== null) {
                                $sections[] = $currentSection;
                            }
                            $sectionId = $extractedSectionId;
                            $currentSection = [
                                'title' => $properties['section_title_section_' . $sectionId] ?? 'Section',
                                'rows' => []
                            ];
                        }
                        
                        // Add row to current section
                        $nextNode = $nextNodes[$outputKey] ?? null;
                        $currentSection['rows'][] = [
                            'id' => $rowId,
                            'row_id' => $rowId, // Ensure backward compatibility
                            'title' => $output['label'] ?? '',
                            'description' => $properties['row_description_section_' . $sectionId . '_row_' . $rowId] ?? '',
                            'next_node' => $nextNode
                        ];
                    }
                }
            }
            
            // Add the last section
            if ($currentSection !== null) {
                $sections[] = $currentSection;
            }
            
            $payload['list_data'] = [
                'button_text' => 'Select an option',
                'sections' => $sections
            ];
            
            \Illuminate\Support\Facades\Log::info('Extracted list data from flow structure', [
                'sections_count' => count($sections),
                'total_rows' => array_sum(array_map(function($section) { 
                    return count($section['rows']); 
                }, $sections))
            ]);
            
            return $payload;
        }
        
        return null;
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

            // Add standard WhatsApp bot flow outputs for all nodes (except goto nodes)
            if ($node['type'] !== 'goto') {
                $operators[$node['id']]['properties']['outputs']['no_input'] = [
                    'label' => 'No Input'
                ];
                $operators[$node['id']]['properties']['outputs']['no_match'] = [
                    'label' => 'No Match'
                ];
                $operators[$node['id']]['properties']['outputs']['delivery_failed'] = [
                    'label' => 'Delivery Failed'
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

    /**
     * Recursively update context_flow_uid in data structure
     *
     * @param mixed $data
     * @param string $newUid
     * @return mixed
     */
    private function updateContextFlowUidInData($data, $newUid)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'context_flow_uid') {
                    $data[$key] = $newUid;
                } else {
                    $data[$key] = $this->updateContextFlowUidInData($value, $newUid);
                }
            }
        } elseif (is_string($data)) {
            // Also update context_flow_uid in JSON strings
            $data = preg_replace(
                '/"context_flow_uid":\s*"[^"]+"/',
                '"context_flow_uid": "'.$newUid.'"',
                $data
            );
        }
        
        return $data;
    }

}