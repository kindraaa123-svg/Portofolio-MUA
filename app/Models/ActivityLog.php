<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_level',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'geo_location',
        'latitude',
        'longitude',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function description(): string
    {
        $actor = $this->user_name ?: ($this->user?->name ?? 'System');
        $module = Str::of((string) $this->module)->replace('-', ' ')->lower()->toString();
        $action = Str::of((string) $this->action)->lower()->toString();
        $properties = $this->properties ?? [];

        if ($this->module === 'auth' && $action === 'login') {
            return "User {$actor} logged in";
        }

        if ($this->module === 'auth' && $action === 'logout') {
            return "User {$actor} logged out";
        }

        $verbMap = [
            'create' => 'created',
            'create-public' => 'created',
            'update' => 'updated',
            'update-status' => 'updated',
            'update-sidebar-permissions' => 'updated',
            'delete' => 'deleted',
            'restore' => 'restored',
            'view' => 'viewed',
            'export' => 'exported',
            'export-xlsx' => 'exported',
            'export-database' => 'exported',
            'import-xlsx' => 'imported',
            'import-database' => 'imported',
            'verify-payment' => 'verified',
            'create-slot' => 'created',
            'create-blocked-schedule' => 'created',
            'reset-password' => 'reset',
        ];

        $verb = $verbMap[$action] ?? 'did';

        $target = match ($action) {
            'verify-payment' => 'booking payment',
            'update-status' => 'booking status',
            'update-sidebar-permissions' => 'access permissions',
            'reset-password' => 'user password',
            default => trim(($module !== '' ? $module : 'system') . ' data'),
        };

        $detail = collect([
            $properties['title'] ?? null,
            $properties['name'] ?? null,
            $properties['question'] ?? null,
            $properties['email'] ?? null,
            $properties['booking_code'] ?? null,
            $properties['file_name'] ?? null,
            $properties['from'] ?? null,
            $properties['reason'] ?? null,
            $properties['error'] ?? null,
        ])->first(fn ($value) => filled($value));

        return $detail
            ? "User {$actor} {$verb} {$target}: {$detail}"
            : "User {$actor} {$verb} {$target}";
    }
}
