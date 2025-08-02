<?php

namespace App\Yantrana\Components\BotReply\Services\NodeTypeHandlers;

use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;

/**
 * Handler for WhatsApp template type nodes
 */
class WhatsAppTemplateNodeHandler extends BaseNodeHandler
{
    /**
     * @var WhatsAppTemplateRepository
     */
    protected $whatsAppTemplateRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->whatsAppTemplateRepository = new WhatsAppTemplateRepository();
    }

    /**
     * Process the node and return the response
     *
     * @param array $node
     * @param array $context
     * @return array
     */
    public function process($node, $context = [])
    {
        $payload = $node['payload'] ?? [];
        $text = $this->processDynamicVariables($payload['text'] ?? '', $this->getAllVariables($context));
        
        // Get available WhatsApp templates for the vendor
        $vendorId = $context['vendor_id'] ?? getVendorId();
        $templates = $this->whatsAppTemplateRepository->fetchItAll([
            'vendors__id' => $vendorId,
            'status' => 'APPROVED'
        ]);

        // Format templates for display
        $templateList = [];
        foreach ($templates as $template) {
            $templateList[] = [
                'id' => $template->_id,
                'name' => $template->template_name,
                'language' => $template->language,
                'category' => $template->category,
                'status' => $template->status
            ];
        }

        return [
            'type' => 'whatsapp_template',
            'text' => $text,
            'templates' => $templateList,
            'next_node' => $payload['next_node'] ?? null,
            'requires_input' => false
        ];
    }

    /**
     * Validate node payload
     *
     * @param array $payload
     * @return array
     */
    public function validatePayload($payload)
    {
        $errors = [];

        if (empty($payload['text'])) {
            $errors[] = 'Text is required for WhatsApp template nodes';
        }

        return $errors;
    }

    /**
     * Get the next node ID based on user input
     *
     * @param array $node
     * @param string|null $userInput
     * @return string|null
     */
    public function getNextNodeId($node, $userInput = null)
    {
        $payload = $node['payload'] ?? [];
        
        // For WhatsApp template nodes, we typically just proceed to the next node
        // since they don't require user input - they just display available templates
        return $payload['next_node'] ?? null;
    }

    /**
     * Check if this node type requires user input
     *
     * @return bool
     */
    public function requiresUserInput()
    {
        return false; // WhatsApp template nodes don't require user input
    }

    /**
     * Get node type identifier
     *
     * @return string
     */
    public function getType()
    {
        return 'whatsapp_template';
    }

    /**
     * Process user input for this node (if needed)
     *
     * @param array $node
     * @param string $userInput
     * @param array $context
     * @return array
     */
    public function processUserInput($node, $userInput, $context = [])
    {
        // WhatsApp template nodes typically don't process user input
        // They just display available templates and move to the next node
        return [
            'context' => $context,
            'next_node' => $this->getNextNodeId($node, $userInput),
            'processed_input' => $userInput
        ];
    }
}