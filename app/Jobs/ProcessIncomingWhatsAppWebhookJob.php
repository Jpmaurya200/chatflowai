<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;

/**
 * Background Job for Processing Incoming WhatsApp Webhook Events
 * 
 * Offloads message parsing, media downloads, contact updates,
 * and bot reply processing from the main HTTP webhook response thread.
 */
class ProcessIncomingWhatsAppWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Webhook payload data
     *
     * @var array
     */
    protected $payload;

    /**
     * Vendor UID
     *
     * @var string
     */
    protected $vendorUid;

    /**
     * Create a new job instance.
     *
     * @param array $payload
     * @param string $vendorUid
     */
    public function __construct(array $payload, string $vendorUid)
    {
        $this->payload = $payload;
        $this->vendorUid = $vendorUid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::info('ProcessIncomingWhatsAppWebhookJob: Starting execution', [
                'vendor_uid' => $this->vendorUid
            ]);

            $engine = app()->make(WhatsAppServiceEngine::class);
            $engine->processWebhookData($this->payload, $this->vendorUid);

            Log::info('ProcessIncomingWhatsAppWebhookJob: Finished execution', [
                'vendor_uid' => $this->vendorUid
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessIncomingWhatsAppWebhookJob: Failed', [
                'vendor_uid' => $this->vendorUid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
