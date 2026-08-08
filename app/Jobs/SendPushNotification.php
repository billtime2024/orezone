<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $device = $this->user->devices->first();

        if ($device && isset($device->device_token)) {
            \Log::info("Push notification sent to {$device->device_token}: {$this->title}");
        }
    }

    public function retryOnFailure(): int
    {
        return 3;
    }

    public function backoff(): array
    {
        return [30];
    }
}
