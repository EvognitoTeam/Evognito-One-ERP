<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformSettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'platform_name' =>
            PlatformSetting::getValue(
                'platform.name',
                'Evognito One'
            ),

            'support_email' =>
            PlatformSetting::getValue(
                'platform.support_email',
                ''
            ),

            'support_phone' =>
            PlatformSetting::getValue(
                'platform.support_phone',
                ''
            ),

            'default_currency' =>
            PlatformSetting::getValue(
                'platform.default_currency',
                'IDR'
            ),

            'default_timezone' =>
            PlatformSetting::getValue(
                'platform.default_timezone',
                'Asia/Jakarta'
            ),

            'default_locale' =>
            PlatformSetting::getValue(
                'platform.default_locale',
                'id'
            ),

            'registration_mode' =>
            PlatformSetting::getValue(
                'registration.mode',
                'invite_only'
            ),

            'default_invite_expiry_hours' =>
            PlatformSetting::getValue(
                'registration.default_invite_expiry_hours',
                72
            ),

            'invoice_generation_day' =>
            PlatformSetting::getValue(
                'billing.invoice_generation_day',
                25
            ),

            'invoice_due_days' =>
            PlatformSetting::getValue(
                'billing.invoice_due_days',
                7
            ),

            'default_tax_percent' =>
            PlatformSetting::getValue(
                'billing.default_tax_percent',
                0
            ),

            'access_lock_after_due_days' =>
            PlatformSetting::getValue(
                'billing.access_lock_after_due_days',
                0
            ),

            'audit_retention_days' =>
            PlatformSetting::getValue(
                'security.audit_retention_days',
                365
            ),
        ];

        return view(
            'superadmin.platform-settings.index',
            compact('settings')
        );
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'platform_name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'support_email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'support_phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'default_currency' => [
                    'required',
                    Rule::in([
                        'IDR',
                        'USD',
                    ]),
                ],

                'default_timezone' => [
                    'required',
                    'timezone',
                ],

                'default_locale' => [
                    'required',
                    Rule::in([
                        'id',
                        'en',
                    ]),
                ],

                'registration_mode' => [
                    'required',
                    Rule::in([
                        'invite_only',
                        'approval',
                        'open',
                    ]),
                ],

                'default_invite_expiry_hours' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:2160',
                ],

                'invoice_generation_day' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:28',
                ],

                'invoice_due_days' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:60',
                ],

                'default_tax_percent' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'access_lock_after_due_days' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:365',
                ],

                'audit_retention_days' => [
                    'required',
                    'integer',
                    'min:30',
                    'max:3650',
                ],
            ]);

        $this->saveSettings(
            $validated
        );

        return back()->with(
            'success',
            'Platform settings berhasil diperbarui.'
        );
    }

    protected function saveSettings(
        array $data
    ): void {
        PlatformSetting::setValue(
            'platform.name',
            $data['platform_name'],
            [
                'group' =>
                'general',

                'label' =>
                'Platform Name',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'platform.support_email',
            $data['support_email'] ?: null,
            [
                'group' =>
                'general',

                'label' =>
                'Support Email',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'platform.support_phone',
            $data['support_phone'] ?: null,
            [
                'group' =>
                'general',

                'label' =>
                'Support Phone',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'platform.default_currency',
            $data['default_currency'],
            [
                'group' =>
                'general',

                'label' =>
                'Default Currency',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'platform.default_timezone',
            $data['default_timezone'],
            [
                'group' =>
                'general',

                'label' =>
                'Default Timezone',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'platform.default_locale',
            $data['default_locale'],
            [
                'group' =>
                'general',

                'label' =>
                'Default Locale',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'registration.mode',
            $data['registration_mode'],
            [
                'group' =>
                'registration',

                'label' =>
                'Registration Mode',

                'type' =>
                'string',
            ]
        );

        PlatformSetting::setValue(
            'registration.default_invite_expiry_hours',
            (int) $data['default_invite_expiry_hours'],
            [
                'group' =>
                'registration',

                'label' =>
                'Default Invite Expiry',

                'type' =>
                'integer',
            ]
        );

        PlatformSetting::setValue(
            'billing.invoice_generation_day',
            (int) $data['invoice_generation_day'],
            [
                'group' =>
                'billing',

                'label' =>
                'Invoice Generation Day',

                'type' =>
                'integer',
            ]
        );

        PlatformSetting::setValue(
            'billing.invoice_due_days',
            (int) $data['invoice_due_days'],
            [
                'group' =>
                'billing',

                'label' =>
                'Invoice Due Days',

                'type' =>
                'integer',
            ]
        );

        PlatformSetting::setValue(
            'billing.default_tax_percent',
            (float) $data['default_tax_percent'],
            [
                'group' =>
                'billing',

                'label' =>
                'Default Tax',

                'type' =>
                'decimal',
            ]
        );

        PlatformSetting::setValue(
            'billing.access_lock_after_due_days',
            (int) $data['access_lock_after_due_days'],
            [
                'group' =>
                'billing',

                'label' =>
                'Access Lock After Due',

                'type' =>
                'integer',
            ]
        );

        PlatformSetting::setValue(
            'security.audit_retention_days',
            (int) $data['audit_retention_days'],
            [
                'group' =>
                'security',

                'label' =>
                'Audit Retention',

                'type' =>
                'integer',
            ]
        );
    }
}
