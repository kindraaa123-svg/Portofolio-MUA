<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ActivityLogger
{
    public static function log(string $module, string $action, mixed $subject = null, array $properties = []): void
    {
        $request = request();
        $user = Auth::user();

        $latitude = self::toNullableFloat($request->input('_latitude', $request->header('X-Latitude')));
        $longitude = self::toNullableFloat($request->input('_longitude', $request->header('X-Longitude')));

        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $user?->name,
                'user_level' => $user?->role?->name,
                'module' => $module,
                'action' => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->id,
                'properties' => $properties,
                'ip_address' => $request->ip(),
                'geo_location' => $request->input(
                    '_geo_location',
                    $request->header('X-Geo-Location', $request->header('CF-IPCountry'))
                ),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (Throwable) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'module' => $module,
                'action' => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->id,
                'properties' => $properties,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }
    }

    protected static function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
