<?php

namespace LaveroAdmin\Admin\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class NotificationChannel extends Model
{
    public const CACHE_KEY = 'lavero_notification_channels_all';

    protected $fillable = ['name', 'driver', 'config', 'events', 'is_active'];

    protected $casts = [
        'config'    => 'array',
        'events'    => 'array',
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('admin.database.connection') ?: config('database.default'));
    }

    public static function getActiveCached(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function (): array {
                $instance = new static();
                if (!Schema::connection($instance->getConnectionName())->hasTable('notification_channels')) {
                    return [];
                }
                return static::where('is_active', true)->get()->toArray();
            });
        } catch (\Throwable) {
            return [];
        }
    }

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }
}
