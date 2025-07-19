<?php

namespace App\Yantrana\Components\BotReply\Services;

use App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\QuestionNodeHandler;
use App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\InteractiveNodeHandler;
use App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\MessageNodeHandler;
use App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\GotoNodeHandler;
use App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\MediaNodeHandler;

/**
 * Factory class for creating node handlers
 */
class NodeHandlerFactory
{
    /**
     * Available node handlers
     *
     * @var array
     */
    private static $handlers = [
        'question' => QuestionNodeHandler::class,
        'interactive' => InteractiveNodeHandler::class,
        'message' => MessageNodeHandler::class,
        'goto' => GotoNodeHandler::class,
        'media' => MediaNodeHandler::class,
    ];

    /**
     * Get handler for a specific node type
     *
     * @param string $nodeType
     * @return \App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\BaseNodeHandler
     * @throws \InvalidArgumentException
     */
    public static function getHandler($nodeType)
    {
        if (!isset(self::$handlers[$nodeType])) {
            throw new \InvalidArgumentException("Unknown node type: $nodeType");
        }

        $handlerClass = self::$handlers[$nodeType];
        return new $handlerClass();
    }

    /**
     * Get handler for a node
     *
     * @param array $node
     * @return \App\Yantrana\Components\BotReply\Services\NodeTypeHandlers\BaseNodeHandler
     * @throws \InvalidArgumentException
     */
    public static function getHandlerForNode($node)
    {
        $nodeType = $node['type'] ?? null;
        
        if (!$nodeType) {
            throw new \InvalidArgumentException("Node type is required");
        }

        return self::getHandler($nodeType);
    }

    /**
     * Get all available node types
     *
     * @return array
     */
    public static function getAvailableNodeTypes()
    {
        return array_keys(self::$handlers);
    }

    /**
     * Check if a node type is supported
     *
     * @param string $nodeType
     * @return bool
     */
    public static function isSupported($nodeType)
    {
        return isset(self::$handlers[$nodeType]);
    }

    /**
     * Register a new node handler
     *
     * @param string $nodeType
     * @param string $handlerClass
     * @return void
     */
    public static function registerHandler($nodeType, $handlerClass)
    {
        self::$handlers[$nodeType] = $handlerClass;
    }

    /**
     * Process a node using its appropriate handler
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public static function processNode($node, $context = [])
    {
        $handler = self::getHandlerForNode($node);
        return $handler->process($node, $context);
    }

    /**
     * Validate a node using its appropriate handler
     *
     * @param array $node
     * @return array
     */
    public static function validateNode($node)
    {
        try {
            $handler = self::getHandlerForNode($node);
            return $handler->validatePayload($node['payload'] ?? []);
        } catch (\Exception $e) {
            return ['Invalid node structure: ' . $e->getMessage()];
        }
    }

    /**
     * Get next node ID using appropriate handler
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public static function getNextNodeId($node, $userInput = null)
    {
        try {
            $handler = self::getHandlerForNode($node);
            return $handler->getNextNodeId($node, $userInput);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if node requires user input
     *
     * @param array $node
     * @return bool
     */
    public static function requiresUserInput($node)
    {
        try {
            $handler = self::getHandlerForNode($node);
            return $handler->requiresUserInput();
        } catch (\Exception $e) {
            return false;
        }
    }
}
