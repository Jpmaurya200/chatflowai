<?php
/**
* BotFlow.php - Model file
*
* This file is part of the BotReply component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\BotReply\Models;

use App\Yantrana\Base\BaseModel;

class BotFlowModel extends BaseModel
{
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "bot_flows";

    /**
     * @var  array $casts - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'status' => 'integer',
    ];

    /**
     * @var  array $fillable - The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'start_trigger',
        'trigger_type',
        'status',
        'vendors__id',
        '__data'
    ];

     /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            // flow builder data (legacy format)
            'flow_builder_data' => 'array',
            // new node-based flow structure
            'flow_nodes_data' => 'array',
        ],
    ];

    /**
     * Get flow data in new node-based format
     *
     * @return array|null
     */
    public function getFlowNodesData()
    {
        return $this->__data['flow_nodes_data'] ?? null;
    }

    /**
     * Set flow data in new node-based format
     *
     * @param array $flowData
     * @return void
     */
    public function setFlowNodesData($flowData)
    {
        $data = $this->__data ?? [];
        $data['flow_nodes_data'] = $flowData;
        $this->__data = $data;
    }

    /**
     * Get legacy flow builder data
     *
     * @return array|null
     */
    public function getFlowBuilderData()
    {
        return $this->__data['flow_builder_data'] ?? null;
    }

    /**
     * Check if flow uses new node-based structure
     *
     * @return bool
     */
    public function usesNewFlowStructure()
    {
        return !empty($this->__data['flow_nodes_data']);
    }

    /**
     * Check if flow uses legacy structure
     *
     * @return bool
     */
    public function usesLegacyFlowStructure()
    {
        return !empty($this->__data['flow_builder_data']) && empty($this->__data['flow_nodes_data']);
    }
}