<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Yantrana\Components\BotReply\Models\BotFlowModel;

class UserActiveFlow extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_active_flows';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'flow_id',
        'phone_number',
        'activated_at',
        'current_node_uid',
        'next_node_uid',
        '__data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'activated_at' => 'datetime',
        '__data' => 'array',
    ];

    /**
     * Get the flow that is active.
     */
    public function flow()
    {
        return $this->belongsTo(BotFlowModel::class, 'flow_id', '_id');
    }

    /**
     * Get the user that owns the active flow.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set a flow as active for a user
     *
     * @param int $userId
     * @param int $flowId
     * @param string $phoneNumber
     * @return UserActiveFlow
     */
    public static function setActiveFlow($userId, $flowId, $phoneNumber, $currentNodeUid = null)
    {
        // Deactivate any existing active flows for this user/phone
        self::where('phone_number', $phoneNumber)->delete();

        // Create new active flow
        return self::create([
            'user_id' => $userId,
            'flow_id' => $flowId,
            'phone_number' => $phoneNumber,
            'activated_at' => now(),
            'current_node_uid' => $currentNodeUid,
            '__data' => []
        ]);
    }

    /**
     * Get active flow for a phone number
     *
     * @param string $phoneNumber
     * @return UserActiveFlow|null
     */
    public static function getActiveFlow($phoneNumber)
    {
        return self::where('phone_number', $phoneNumber)
            ->latest('activated_at')
            ->first();
    }

    /**
     * Set the last sent bot reply ID for flow scoping
     *
     * @param string $phoneNumber
     * @param string $botReplyId
     * @return bool
     */
    public static function setLastSentReplyId($phoneNumber, $botReplyId)
    {
        $activeFlow = self::getActiveFlow($phoneNumber);
        
        if ($activeFlow) {
            $data = $activeFlow->__data ?? [];
            $data['last_sent_reply_id'] = $botReplyId;
            
            $activeFlow->__data = $data;
            return $activeFlow->save();
        }
        
        return false;
    }

    /**
     * Get the last sent bot reply ID for flow scoping
     *
     * @param string $phoneNumber
     * @return string|null
     */
    public static function getLastSentReplyId($phoneNumber)
    {
        $activeFlow = self::getActiveFlow($phoneNumber);
        
        if ($activeFlow && isset($activeFlow->__data['last_sent_reply_id'])) {
            return $activeFlow->__data['last_sent_reply_id'];
        }
        
        return null;
    }

    /**
     * Clear the last sent reply ID (useful when flow ends)
     *
     * @param string $phoneNumber
     * @return bool
     */
    public static function clearLastSentReplyId($phoneNumber)
    {
        $activeFlow = self::getActiveFlow($phoneNumber);
        
        if ($activeFlow) {
            $data = $activeFlow->__data ?? [];
            unset($data['last_sent_reply_id']);
            
            $activeFlow->__data = $data;
            return $activeFlow->save();
        }
        
        return false;
    }

    /**
     * Update the current node UID for an active flow
     *
     * @param string $phoneNumber
     * @param string $currentNodeUid
     * @return bool
     */
    public static function updateCurrentNode($phoneNumber, $currentNodeUid)
    {
        $activeFlow = self::getActiveFlow($phoneNumber);
        
        if ($activeFlow) {
            $activeFlow->current_node_uid = $currentNodeUid;
            return $activeFlow->save();
        }
        
        return false;
    }

    /**
     * Get the current node UID for an active flow
     *
     * @param string $phoneNumber
     * @return string|null
     */
    public static function getCurrentNode($phoneNumber)
    {
        $activeFlow = self::getActiveFlow($phoneNumber);
        
        return $activeFlow ? $activeFlow->current_node_uid : null;
    }
}
