<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use App\Models\NotificationProvider;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60; // 1 minute timeout for SMS
    public $tries = 3; // Retry 3 times on failure
    public $backoff = [30, 60, 120]; // Wait 30s, 60s, 120s between retries

    protected $phoneNumber;
    protected $message;
    protected $userId;
    protected $notificationLink;
    protected $providerId;
    protected $notificationType;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $phoneNumber,
        string $message,
        ?int $userId = null,
        ?string $notificationLink = null,
        ?int $providerId = null,
        string $notificationType = 'queued'
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->userId = $userId;
        $this->notificationLink = $notificationLink;
        $this->providerId = $providerId;
        $this->notificationType = $notificationType;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService)
    {
        try {
            // Get provider if specified
            $provider = null;
            if ($this->providerId) {
                $provider = NotificationProvider::find($this->providerId);
            }

            // Send SMS using NotificationService
            $smsResult = $notificationService->sendSMS($this->phoneNumber, $this->message, $provider);

            if ($smsResult) {
                // Log SMS sent activity
                $loggedUserId = $this->userId ?? Auth::id();
                ActivityLogService::logSMSSent(
                    $this->phoneNumber,
                    $this->message,
                    $loggedUserId,
                    $this->userId,
                    [
                        'notification_type' => $this->notificationType,
                        'link' => $this->notificationLink,
                        'queued' => true,
                        'provider_id' => $this->providerId,
                    ]
                );

                Log::info('SMS sent successfully via queue', [
                    'phone' => $this->phoneNumber,
                    'user_id' => $this->userId,
                    'message_length' => strlen($this->message)
                ]);
            } else {
                // SMS sending failed, will retry automatically
                Log::warning('SMS sending failed in queue job', [
                    'phone' => $this->phoneNumber,
                    'user_id' => $this->userId,
                    'attempt' => $this->attempts()
                ]);
                
                // Throw exception to trigger retry
                throw new \Exception('SMS sending returned false');
            }
        } catch (\Exception $e) {
            Log::error('SMS queue job exception', [
                'phone' => $this->phoneNumber,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SMS queue job failed after all retries', [
            'phone' => $this->phoneNumber,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}

