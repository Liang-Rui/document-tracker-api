<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\DocumentExpiryDigestNotification;

class SendDocumentExpiryDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expiry_date = Carbon::now()->addDays(7);

        User::withWhereHas('documents', function ($q) use($expiry_date) {
            $q->whereNull('archived_at')
                ->whereDate('expires_at', '<=', $expiry_date);
        })->each(function ($owner) {
            $owner->notify(new DocumentExpiryDigestNotification($owner->documents));
        });
    }
}
