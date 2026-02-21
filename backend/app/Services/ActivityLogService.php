<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log(
        string  $action,
        ?Model  $subject    = null,
        ?array  $oldValues  = null,
        ?array  $newValues  = null,
        ?string $description = null,
    ): void {
        try {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => $action,
                'model_type'  => $subject ? get_class($subject) : null,
                'model_id'    => $subject?->getKey(),
                'old_values'  => $oldValues,
                'new_values'  => $newValues,
                'ip_address'  => Request::ip(),
                'user_agent'  => substr(Request::userAgent() ?? '', 0, 255),
                'description' => $description,
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Never let logging crash the app
        }
    }

    public static function logCreated(Model $subject, ?array $newValues = null): void
    {
        $name = class_basename($subject);
        self::log("created_{$name}", $subject, null, $newValues ?? $subject->toArray(), "Created {$name} #{$subject->getKey()}");
    }

    public static function logUpdated(Model $subject, array $oldValues, array $newValues): void
    {
        $name = class_basename($subject);
        self::log("updated_{$name}", $subject, $oldValues, $newValues, "Updated {$name} #{$subject->getKey()}");
    }

    public static function logDeleted(Model $subject): void
    {
        $name = class_basename($subject);
        self::log("deleted_{$name}", $subject, $subject->toArray(), null, "Deleted {$name} #{$subject->getKey()}");
    }
}