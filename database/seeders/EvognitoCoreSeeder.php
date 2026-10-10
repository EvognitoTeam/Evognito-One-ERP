<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class EvognitoCoreSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('evognito.superadmin.email');
        $password = config('evognito.superadmin.password');

        if (!$email) {
            throw new RuntimeException(
                'EVOGNITO_SUPERADMIN_EMAIL belum diatur di .env'
            );
        }

        if (!$password) {
            throw new RuntimeException(
                'EVOGNITO_SUPERADMIN_PASSWORD belum diatur di .env'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Superadmin
        |--------------------------------------------------------------------------
        */

        $superadmin = User::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'ulid' => (string) Str::ulid(),
                'name' => config('evognito.superadmin.name'),
                'username' => config('evognito.superadmin.username'),
                'password' => Hash::make($password),
                'status' => 'active',
                'is_super_admin' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Platform Owner Role
        |--------------------------------------------------------------------------
        */

        $platformOwnerRole = Role::firstOrCreate(
            [
                'organization_id' => null,
                'scope' => 'platform',
                'code' => 'platform_owner',
            ],
            [
                'ulid' => (string) Str::ulid(),
                'name' => 'Platform Owner',
                'description' => 'Pemilik dan pengelola penuh EvognitoOne.',
                'is_system' => true,
            ]
        );

        DB::table('user_roles')->updateOrInsert(
            [
                'user_id' => $superadmin->id,
                'role_id' => $platformOwnerRole->id,
                'organization_id' => null,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Core Modules
        |--------------------------------------------------------------------------
        */

        $modules = [
            [
                'code' => 'hris',
                'name' => 'HRIS',
                'description' => 'Human Resource Information System',
                'category' => 'People',
                'icon' => 'users',
                'type' => 'external',
                'entry_url' => null,
                'sort_order' => 10,
            ],
            [
                'code' => 'performance',
                'name' => 'Performance',
                'description' => 'KPI & Performance Appraisal',
                'category' => 'People',
                'icon' => 'chart-line',
                'type' => 'external',
                'entry_url' => null,
                'sort_order' => 20,
            ],
            [
                'code' => 'lms',
                'name' => 'LMS',
                'description' => 'Learning Management System',
                'category' => 'Learning',
                'icon' => 'graduation-cap',
                'type' => 'external',
                'entry_url' => null,
                'sort_order' => 30,
            ],
            [
                'code' => 'kansiku',
                'name' => 'Kansiku',
                'description' => 'Attendance & IoT Platform',
                'category' => 'People',
                'icon' => 'fingerprint',
                'type' => 'external',
                'entry_url' => null,
                'sort_order' => 40,
            ],
            [
                'code' => 'sales',
                'name' => 'Sales Force',
                'description' => 'Sales Force Automation',
                'category' => 'Sales',
                'icon' => 'route',
                'type' => 'external',
                'entry_url' => null,
                'sort_order' => 50,
            ],
            [
                'code' => 'sites',
                'name' => 'Sites',
                'description' => 'Website & Company Profile Builder',
                'category' => 'Digital',
                'icon' => 'globe',
                'type' => 'internal',
                'entry_url' => '/sites',
                'sort_order' => 60,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                [
                    'code' => $moduleData['code'],
                ],
                array_merge(
                    $moduleData,
                    [
                        'ulid' => (string) Str::ulid(),
                        'status' => 'active',
                    ]
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Platform Settings
        |--------------------------------------------------------------------------
        */

        DB::table('platform_settings')->updateOrInsert(
            [
                'key' => 'platform.name',
            ],
            [
                'value' => json_encode(
                    'Evognito One'
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('platform_settings')->updateOrInsert(
            [
                'key' => 'platform.currency',
            ],
            [
                'value' => json_encode(
                    'IDR'
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('platform_settings')->updateOrInsert(
            [
                'key' => 'platform.timezone',
            ],
            [
                'value' => json_encode(
                    'Asia/Jakarta'
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
