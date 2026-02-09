<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AttendanceSetting extends Model
{
    public const CACHE_KEY = 'attendance_settings.current';

    protected $fillable = [
        'timezone',
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'late_after',
        'max_upload_mb',
        'allowed_mimes',
    ];

    protected function casts(): array
    {
        return [
            'max_upload_mb' => 'integer',
        ];
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->first() ?? static::query()->create([
                'timezone' => 'Asia/Jakarta',
                'check_in_start' => '05:45',
                'check_in_end' => '07:10',
                'check_out_start' => '15:00',
                'check_out_end' => '16:45',
                'max_upload_mb' => 5,
                'allowed_mimes' => 'image/jpeg,image/png',
            ]);
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            self::forgetCache();
        });

        static::deleted(function () {
            self::forgetCache();
        });
    }
}
