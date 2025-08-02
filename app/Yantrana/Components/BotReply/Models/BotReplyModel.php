<?php
/**
* BotReply.php - Model file
*
* This file is part of the BotReply component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\BotReply\Models;

use App\Yantrana\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Yantrana\Components\BotReply\Models\BotFlowModel;

class BotReplyModel extends BaseModel
{
    /**
     * @var  string $table - The database table used by the model.
     */
    protected $table = "bot_replies";

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
    ];

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            // stores the interactive message data
            'interaction_message' => 'array:extend',
            // store the media message data
            'media_message' => 'array:extend',
            // store the question message data
            'question_message' => 'array:extend',
            // store the goto message data
            'goto_message' => 'array:extend',
            // store the team assignment message data
            'team_assignment_message' => 'array:extend',
            // store the wait message data
            'wait_message' => 'array:extend',
            // store the webhook message data
            'webhook_message' => 'array:extend',
            // store the custom field message data
            'custom_field_message' => 'array:extend',
            // store the stay in session message data
            'stay_in_session_message' => 'array:extend',
        ],
    ];

    /**
     * Connected Bot flow
     *
     * @return BelongsTo
     */
    function botFlow():BelongsTo {
        return $this->belongsTo(BotFlowModel::class, 'bot_flows__id', '_id');
    }
}