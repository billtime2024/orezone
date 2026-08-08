<?php

namespace App\Jobs;

use App\Models\VerificationDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVerificationDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public VerificationDocument $document
    ) {}

    public function handle(): void
    {
        \Log::info("Processing verification document {$this->document->id} of type {$this->document->document_type}");
    }

    public function retryOnFailure(): int
    {
        return 2;
    }
}
