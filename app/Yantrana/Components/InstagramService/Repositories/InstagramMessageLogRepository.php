<?php

/**
 * InstagramMessageLogRepository.php - Repository file
 *
 * This file is part of the Instagram Service component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\InstagramService\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\InstagramService\Models\InstagramMessageLogModel;
use Carbon\Carbon;

class InstagramMessageLogRepository extends BaseRepository
{
    /**
     * Primary model instance
     *
     * @var InstagramMessageLogModel
     */
    protected $primaryModel = InstagramMessageLogModel::class;

    /**
     * Store incoming Instagram message
     *
     * @param array $messageData
     * @return InstagramMessageLogModel|false
     */
    public function storeIncomingMessage($messageData)
    {
        $dataToStore = [
            'vendors__id' => $messageData['vendor_id'],
            'contacts__id' => $messageData['contact_id'],
            'instagram_page_id' => $messageData['page_id'],
            'contact_instagram_id' => $messageData['sender_id'],
            'message_id' => $messageData['message_id'],
            'message' => $messageData['message'] ?? '',
            'status' => 'received',
            'type' => 'incoming',
            'is_incoming_message' => true,
            'message_type' => $messageData['message_type'] ?? 'text',
            'messaged_at' => isset($messageData['timestamp']) 
                ? Carbon::createFromTimestamp($messageData['timestamp']) 
                : Carbon::now(),
            'replied_to_instagram_message_logs__uid' => $messageData['reply_to'] ?? null,
            'is_forwarded' => $messageData['is_forwarded'] ?? false,
            'bot_reply' => false,
            '__data' => [
                'webhook_response' => $messageData['webhook_data'] ?? [],
                'media_data' => $messageData['media_data'] ?? [],
                'contact_data' => $messageData['contact_data'] ?? [],
                'reply_data' => $messageData['reply_data'] ?? [],
                'story_data' => $messageData['story_data'] ?? [],
            ],
        ];

        return $this->storeIt($dataToStore);
    }

    /**
     * Store outgoing Instagram message
     *
     * @param array $messageData
     * @return InstagramMessageLogModel|false
     */
    public function storeOutgoingMessage($messageData)
    {
        $dataToStore = [
            'vendors__id' => $messageData['vendor_id'],
            'contacts__id' => $messageData['contact_id'],
            'instagram_page_id' => $messageData['page_id'],
            'contact_instagram_id' => $messageData['recipient_id'],
            'message_id' => $messageData['message_id'],
            'message' => $messageData['message'] ?? '',
            'status' => $messageData['status'] ?? 'sent',
            'type' => 'outgoing',
            'is_incoming_message' => false,
            'message_type' => $messageData['message_type'] ?? 'text',
            'messaged_at' => Carbon::now(),
            'replied_to_instagram_message_logs__uid' => $messageData['reply_to'] ?? null,
            'bot_reply' => $messageData['bot_reply'] ?? false,
            '__data' => [
                'api_response' => $messageData['api_response'] ?? [],
                'media_data' => $messageData['media_data'] ?? [],
                'options' => $messageData['options'] ?? [],
            ],
        ];

        return $this->storeIt($dataToStore);
    }

    /**
     * Update message status from webhook
     *
     * @param string $messageId
     * @param string $status
     * @param array $webhookData
     * @return bool
     */
    public function updateMessageStatus($messageId, $status, $webhookData = [])
    {
        $message = $this->primaryModel::where('message_id', $messageId)->first();
        
        if (!$message) {
            return false;
        }

        $updateData = ['status' => $status];
        
        if ($status === 'delivered') {
            $updateData['delivered_at'] = Carbon::now();
        } elseif ($status === 'read') {
            $updateData['read_at'] = Carbon::now();
        }

        // Update webhook data
        if (!empty($webhookData)) {
            $existingData = $message->__data ?? [];
            $existingData['webhook_responses'][$status] = $webhookData;
            $updateData['__data'] = $existingData;
        }

        return $this->updateIt($message, $updateData);
    }

    /**
     * Get recent messages for a contact
     *
     * @param int $contactId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContactMessages($contactId, $limit = 50)
    {
        return $this->primaryModel::where('contacts__id', $contactId)
            ->with(['contact', 'repliedToMessage'])
            ->orderBy('messaged_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread messages count for vendor
     *
     * @param int $vendorId
     * @return int
     */
    public function getUnreadMessagesCount($vendorId)
    {
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->where('is_incoming_message', true)
            ->where('status', '!=', 'read')
            ->count();
    }

    /**
     * Get contacts with recent messages
     *
     * @param int $vendorId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContactsWithRecentMessages($vendorId, $limit = 50)
    {
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->with(['contact'])
            ->select('contacts__id', 'contact_instagram_id')
            ->selectRaw('MAX(messaged_at) as last_message_at')
            ->selectRaw('COUNT(CASE WHEN is_incoming_message = 1 AND status != "read" THEN 1 END) as unread_count')
            ->groupBy('contacts__id', 'contact_instagram_id')
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark all messages as read for a contact
     *
     * @param int $contactId
     * @param int $vendorId
     * @return bool
     */
    public function markContactMessagesAsRead($contactId, $vendorId)
    {
        return $this->primaryModel::where('contacts__id', $contactId)
            ->where('vendors__id', $vendorId)
            ->where('is_incoming_message', true)
            ->where('status', '!=', 'read')
            ->update([
                'status' => 'read',
                'read_at' => Carbon::now()
            ]);
    }

    /**
     * Get message by Instagram message ID
     *
     * @param string $messageId
     * @param int $vendorId
     * @return InstagramMessageLogModel|null
     */
    public function getByMessageId($messageId, $vendorId = null)
    {
        $query = $this->primaryModel::where('message_id', $messageId);
        
        if ($vendorId) {
            $query->where('vendors__id', $vendorId);
        }
        
        return $query->first();
    }

    /**
     * Get messages for chat interface
     *
     * @param int $contactId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMessagesForChat($contactId, $page = 1, $perPage = 50)
    {
        return $this->primaryModel::where('contacts__id', $contactId)
            ->with(['repliedToMessage'])
            ->orderBy('messaged_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get message statistics for vendor
     *
     * @param int $vendorId
     * @param string $period (today, week, month)
     * @return array
     */
    public function getMessageStats($vendorId, $period = 'today')
    {
        $startDate = match($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            default => Carbon::today()
        };

        $query = $this->primaryModel::where('vendors__id', $vendorId)
            ->where('messaged_at', '>=', $startDate);

        return [
            'total_messages' => $query->count(),
            'incoming_messages' => $query->where('is_incoming_message', true)->count(),
            'outgoing_messages' => $query->where('is_incoming_message', false)->count(),
            'unread_messages' => $query->where('is_incoming_message', true)
                                     ->where('status', '!=', 'read')->count(),
        ];
    }

    /**
     * Delete old messages (for cleanup)
     *
     * @param int $vendorId
     * @param int $daysOld
     * @return int Number of deleted messages
     */
    public function deleteOldMessages($vendorId, $daysOld = 90)
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->where('messaged_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Search messages by content
     *
     * @param int $vendorId
     * @param string $searchTerm
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchMessages($vendorId, $searchTerm, $limit = 50)
    {
        return $this->primaryModel::where('vendors__id', $vendorId)
            ->where('message', 'LIKE', "%{$searchTerm}%")
            ->with(['contact'])
            ->orderBy('messaged_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
