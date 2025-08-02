<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Yantrana\Components\BotReply\Models\BotFlowModel;
use App\Yantrana\Components\BotReply\BotFlowEngine;

class UpdateBotFlowsForNewStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The __data column already exists and supports JSON, so we don't need schema changes
        // We just need to migrate existing flow data to the new structure
        
        $this->migrateExistingFlows();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the new flow structure data from existing flows
        $flows = BotFlowModel::all();
        
        foreach ($flows as $flow) {
            $data = $flow->__data ?? [];
            if (isset($data['flow_nodes_data'])) {
                unset($data['flow_nodes_data']);
                $flow->__data = $data;
                $flow->save();
            }
        }
    }

    /**
     * Migrate existing flows to new structure
     *
     * @return void
     */
    private function migrateExistingFlows()
    {
        $flows = BotFlowModel::all();
        $botFlowEngine = app(BotFlowEngine::class);
        
        foreach ($flows as $flow) {
            $legacyData = $flow->getFlowBuilderData();
            
            if ($legacyData && !$flow->usesNewFlowStructure()) {
                try {
                    // Convert legacy structure to new node-based structure
                    $newFlowData = $botFlowEngine->convertToNewFlowStructure($legacyData, $flow->_uid);
                    
                    // Save the new structure
                    $flow->setFlowNodesData($newFlowData);
                    $flow->save();
                    
                    echo "Migrated flow: {$flow->title} (ID: {$flow->_uid})\n";
                } catch (\Exception $e) {
                    echo "Failed to migrate flow: {$flow->title} (ID: {$flow->_uid}) - Error: {$e->getMessage()}\n";
                }
            }
        }
    }
}
