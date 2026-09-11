<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Yantrana\Components\Contact\ContactEngine;

/**
 * Background Job for Processing Large Contact Imports
 * 
 * This job allows contact imports to run asynchronously in the background,
 * preventing timeout issues for very large imports (150K+ contacts)
 * 
 * Usage:
 * ProcessContactImportJob::dispatch($filePath, $vendorId, $options);
 */
class ProcessContactImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1; // Don't retry imports - they're expensive

    /**
     * File path to import
     *
     * @var string
     */
    protected $filePath;

    /**
     * Vendor ID
     *
     * @var int
     */
    protected $vendorId;

    /**
     * Import options
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new job instance.
     *
     * @param string $filePath Path to the uploaded file
     * @param int $vendorId Vendor ID
     * @param array $options Additional import options
     * @return void
     */
    public function __construct(string $filePath, int $vendorId, array $options = [])
    {
        $this->filePath = $filePath;
        $this->vendorId = $vendorId;
        $this->options = $options;
        
        // Set queue name from config
        $this->onQueue(config('import-settings.queue_name', 'imports'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        \Log::info("=== BACKGROUND IMPORT JOB STARTED ===");
        \Log::info("File: {$this->filePath}");
        \Log::info("Vendor ID: {$this->vendorId}");
        \Log::info("Job ID: {$this->job->getJobId()}");
        
        try {
            // Create a mock request object
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'document_name' => basename($this->filePath),
                'vendor_id' => $this->vendorId,
            ]);
            
            // Process the import
            $contactEngine = app(ContactEngine::class);
            $result = $contactEngine->processImportContactsOptimized($request);
            
            \Log::info("=== BACKGROUND IMPORT JOB COMPLETED ===");
            \Log::info("Result: " . json_encode($result));
            
            // Optional: Notify vendor via email/notification
            // $this->notifyVendor($result);
            
        } catch (\Throwable $e) {
            \Log::error("=== BACKGROUND IMPORT JOB FAILED ===");
            \Log::error("Error: " . $e->getMessage());
            \Log::error("File: " . $e->getFile());
            \Log::error("Line: " . $e->getLine());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Optional: Notify vendor of failure
            // $this->notifyVendorOfFailure($e);
            
            // Re-throw to mark job as failed
            throw $e;
        } finally {
            // Clean up temporary file if it exists
            if (file_exists($this->filePath)) {
                @unlink($this->filePath);
                \Log::info("Cleaned up temporary file: {$this->filePath}");
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("=== IMPORT JOB PERMANENTLY FAILED ===");
        \Log::error("File: {$this->filePath}");
        \Log::error("Vendor ID: {$this->vendorId}");
        \Log::error("Exception: " . $exception->getMessage());
        
        // Optional: Notify vendor of permanent failure
        // $this->notifyVendorOfFailure($exception);
        
        // Clean up file
        if (file_exists($this->filePath)) {
            @unlink($this->filePath);
        }
    }

    /**
     * Notify vendor of successful import (optional implementation)
     *
     * @param mixed $result
     * @return void
     */
    protected function notifyVendor($result): void
    {
        // TODO: Implement notification
        // Example: Send email or push notification to vendor
        // Mail::to($vendor->email)->send(new ImportCompletedMail($result));
    }

    /**
     * Notify vendor of import failure (optional implementation)
     *
     * @param \Throwable $exception
     * @return void
     */
    protected function notifyVendorOfFailure(\Throwable $exception): void
    {
        // TODO: Implement failure notification
        // Example: Send error email to vendor
        // Mail::to($vendor->email)->send(new ImportFailedMail($exception));
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['import', 'contacts', "vendor:{$this->vendorId}"];
    }
}

