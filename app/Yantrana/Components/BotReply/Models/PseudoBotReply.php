<?php

namespace App\Yantrana\Components\BotReply\Models;

/**
 * Simple class to represent bot flows as bot reply objects for processing
 * This allows bot flows to be processed alongside regular bot replies
 */
class PseudoBotReply
{
    public $_id;
    public $_uid;
    public $reply_trigger;
    public $reply_text;
    public $trigger_type;
    public $priority_index;
    public $__data;
    public $bot_flows__id;
    public $status;
    public $botFlow;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get the primary key for the model (required for Laravel Collections)
     */
    public function getKey()
    {
        return $this->_uid;
    }

    /**
     * Get the key name for the model
     */
    public function getKeyName()
    {
        return '_uid';
    }

    /**
     * Magic getter for dynamic properties
     */
    public function __get($key)
    {
        return property_exists($this, $key) ? $this->$key : null;
    }

    /**
     * Magic setter for dynamic properties
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Check if a property exists
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }
}