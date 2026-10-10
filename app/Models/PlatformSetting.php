<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table =
    'platform_settings';

    protected $fillable = [
        'key',
        'value',

        'group',

        'label',
        'description',

        'type',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' =>
            'boolean',
        ];
    }

    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {
        $record =
            static::query()
            ->where(
                'key',
                $key
            )
            ->first();

        if (!$record) {
            return $default;
        }

        if ($record->value === null) {
            return $default;
        }

        $decoded =
            json_decode(
                $record->value,
                true
            );

        if (
            json_last_error()
            !== JSON_ERROR_NONE
        ) {
            return $record->value;
        }

        return $decoded;
    }

    public static function setValue(
        string $key,
        mixed $value,
        array $attributes = []
    ): void {
        static::query()
            ->updateOrCreate(
                [
                    'key' =>
                    $key,
                ],
                array_merge(
                    [
                        'value' =>
                        json_encode(
                            $value,
                            JSON_UNESCAPED_UNICODE
                                | JSON_UNESCAPED_SLASHES
                        ),
                    ],
                    $attributes
                )
            );
    }
}
