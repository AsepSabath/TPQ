<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        string $module,
        string $action,
        ?Model $target = null,
        ?array $beforeData = null,
        ?array $afterData = null
    ): void {
        try {
            AuditLog::query()->create([
                'user_id' => Auth::id(),
                'module' => $module,
                'action' => $action,
                'target_type' => $target ? $target::class : null,
                'target_id' => $target?->getKey(),
                'before_data' => $beforeData,
                'after_data' => $afterData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Audit must never block business flow.
        }
    }
}
