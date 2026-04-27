<?php

namespace App\Services;

use App\Models\Santri;
use App\Models\WaNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsAppNotifier
{
    public function queueForSantri(Santri $santri, string $templateKey, string $message): ?WaNotification
    {
        $guardian = $santri->guardians()
            ->where('is_whatsapp', true)
            ->orderByDesc('is_primary')
            ->first();

        if (! $guardian) {
            return null;
        }

        return WaNotification::query()->create([
            'santri_id' => $santri->id,
            'guardian_phone' => $guardian->phone,
            'template_key' => $templateKey,
            'message' => $message,
            'status' => 'queued',
            'created_by' => Auth::id(),
        ]);
    }

    public function dispatch(WaNotification $notification): bool
    {
        if (! config('wa.enabled')) {
            return false;
        }

        if (! config('wa.endpoint') || ! config('wa.token')) {
            $notification->update([
                'status' => 'failed',
                'failure_reason' => 'WA endpoint atau token belum dikonfigurasi.',
            ]);

            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withToken((string) config('wa.token'))
                ->post((string) config('wa.endpoint'), [
                    'phone' => $notification->guardian_phone,
                    'message' => $notification->message,
                    'template_key' => $notification->template_key,
                ]);
        } catch (\Throwable $exception) {
            $notification->update([
                'status' => 'failed',
                'failure_reason' => $exception->getMessage(),
            ]);

            return false;
        }

        if ($response->successful()) {
            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failure_reason' => null,
            ]);

            return true;
        }

        $notification->update([
            'status' => 'failed',
            'failure_reason' => $response->body(),
        ]);

        return false;
    }
}
