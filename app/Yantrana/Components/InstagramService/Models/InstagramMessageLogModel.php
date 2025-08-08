<?php

/**
 * InstagramMessageLogModel.php - Model file
 *
 * This file is part of the Instagram Service component.
 *-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\InstagramService\Models;

use App\Yantrana\Base\BaseModel;
use App\Yantrana\Components\Contact\Models\ContactModel;
use App\Yantrana\Components\Vendor\Models\VendorModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstagramMessageLogModel extends BaseModel
{
    /**
     * @var string - The database table used by the model.
     */
    protected $table = 'instagram_message_logs';

    /**
     * Let the system knows Text columns treated as JSON
     *
     * @var array
     *----------------------------------------------------------------------- */
    protected $jsonColumns = [
        '__data' => [
            'contact_data' => 'array',
            'webhook_response' => 'array',
            'media_data' => 'array',
            'reply_data' => 'array',
            'story_data' => 'array',
            'reaction_data' => 'array',
            'options' => 'array:extend',
        ],
    ];

    /**
     * @var array - The attributes that should be casted to native types.
     */
    protected $casts = [
        '__data' => 'array',
        'messaged_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'is_incoming_message' => 'boolean',
        'is_forwarded' => 'boolean',
        'bot_reply' => 'boolean',
        'disable_ai_bot' => 'boolean',
    ];

    /**
     * @var array - The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendors__id',
        'contacts__id',
        'instagram_page_id',
        'contact_instagram_id',
        'message_id',
        'message',
        'status',
        'type',
        'is_incoming_message',
        'message_type',
        'messaged_at',
        'delivered_at',
        'read_at',
        'replied_to_instagram_message_logs__uid',
        'is_forwarded',
        'bot_reply',
        'disable_ai_bot',
        '__data',
    ];

    /**
     * Appended attributes
     */
    protected $appends = [
        'formatted_message',
        'time_ago',
        'is_media_message',
    ];

    /**
     * Get the vendor that owns the message
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendors__id', '_id');
    }

    /**
     * Get the contact that owns the message
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContactModel::class, 'contacts__id', '_id');
    }

    /**
     * Get the message this message is replying to
     */
    public function repliedToMessage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replied_to_instagram_message_logs__uid', '_uid');
    }

    /**
     * Get formatted message attribute
     */
    public function getFormattedMessageAttribute()
    {
        if (empty($this->message)) {
            // Handle media messages
            if ($this->message_type !== 'text') {
                return ucfirst($this->message_type) . ' message';
            }
            return 'No content';
        }

        return $this->message;
    }

    /**
     * Get time ago attribute
     */
    public function getTimeAgoAttribute()
    {
        return $this->messaged_at ? $this->messaged_at->diffForHumans() : null;
    }

    /**
     * Check if message is media message
     */
    public function getIsMediaMessageAttribute()
    {
        return in_array($this->message_type, ['image', 'video', 'audio', 'file', 'story_reply']);
    }

    /**
     * Scope for incoming messages
     */
    public function scopeIncoming($query)
    {
        return $query->where('is_incoming_message', true);
    }

    /**
     * Scope for outgoing messages
     */
    public function scopeOutgoing($query)
    {
        return $query->where('is_incoming_message', false);
    }

    /**
     * Scope for specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendors__id', $vendorId);
    }

    /**
     * Scope for specific contact
     */
    public function scopeForContact($query, $contactId)
    {
        return $query->where('contacts__id', $contactId);
    }

    /**
     * Scope for recent messages
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('messaged_at', 'desc')->limit($limit);
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('status', '!=', 'read')
                    ->where('is_incoming_message', true);
    }

    /**
     * Scope for specific message type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Get media data from __data column
     */
    public function getMediaData()
    {
        return $this->__data['media_data'] ?? null;
    }

    /**
     * Get webhook response data
     */
    public function getWebhookResponse()
    {
        return $this->__data['webhook_response'] ?? null;
    }

    /**
     * Check if message has media attachment
     */
    public function hasMediaAttachment()
    {
        $mediaData = $this->getMediaData();
        return !empty($mediaData['url']) || !empty($mediaData['attachment_id']);
    }

    /**
     * Get media URL if available
     */
    public function getMediaUrl()
    {
        $mediaData = $this->getMediaData();
        return $mediaData['url'] ?? null;
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => Carbon::now()
        ]);
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => Carbon::now()
        ]);
    }

    /**
     * Check if message is from bot
     */
    public function isFromBot()
    {
        return $this->bot_reply === true;
    }

    /**
     * Check if message is a reply
     */
    public function isReply()
    {
        return !empty($this->replied_to_instagram_message_logs__uid);
    }

    /**
     * Get contact name for display
     */
    public function getContactDisplayName()
    {
        if ($this->contact) {
            return $this->contact->full_name ?: $this->contact->instagram_username ?: 'Unknown User';
        }
        return 'Unknown User';
    }
}
