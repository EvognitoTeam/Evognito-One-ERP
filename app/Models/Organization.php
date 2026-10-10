<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'type',
        'name',
        'slug',
        'legal_name',
        'email',
        'phone',
        'address',
        'logo',
        'timezone',
        'locale',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Organization $organization): void {
            if (empty($organization->ulid)) {
                $organization->ulid = (string) Str::ulid();
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'organization_users'
        )
            ->withPivot([
                'status',
                'is_default',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function modules()
    {
        return $this->belongsToMany(
            Module::class,
            'organization_modules',
            'organization_id',
            'module_id'
        )
            ->withPivot([
                'enabled',
                'status',
                'started_at',
                'expires_at',
                'settings',
            ])
            ->withTimestamps();
    }
}
