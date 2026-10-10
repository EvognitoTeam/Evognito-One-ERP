<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search =
            trim(
                (string) $request->get(
                    'search'
                )
            );

        $action =
            trim(
                (string) $request->get(
                    'action'
                )
            );

        $userId =
            $request->integer(
                'user_id'
            );

        $dateFrom =
            $request->get(
                'date_from'
            );

        $dateTo =
            $request->get(
                'date_to'
            );

        $logs =
            AuditLog::query()
            ->with([
                'user',
                'organization',
            ])

            ->when(
                $search !== '',
                function ($query) use (
                    $search
                ) {
                    $query->where(
                        function (
                            $subQuery
                        ) use (
                            $search
                        ) {
                            $subQuery
                                ->where(
                                    'event',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'subject_type',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    fn($userQuery) =>
                                    $userQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            )

            ->when(
                $action !== '',
                fn($query) =>
                $query->where(
                    'action',
                    $action
                )
            )

            ->when(
                $userId > 0,
                fn($query) =>
                $query->where(
                    'user_id',
                    $userId
                )
            )

            ->when(
                $dateFrom,
                fn($query) =>
                $query->whereDate(
                    'created_at',
                    '>=',
                    $dateFrom
                )
            )

            ->when(
                $dateTo,
                fn($query) =>
                $query->whereDate(
                    'created_at',
                    '<=',
                    $dateTo
                )
            )

            ->latest(
                'created_at'
            )
            ->paginate(30)
            ->withQueryString();

        $users =
            User::query()
            ->where(
                'is_super_admin',
                true
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        return view(
            'superadmin.audit-logs.index',
            compact(
                'logs',
                'users',
                'search',
                'action',
                'userId',
                'dateFrom',
                'dateTo'
            )
        );
    }

    public function show(
        AuditLog $auditLog
    ): View {
        $auditLog->load([
            'user',
            'organization',
        ]);

        return view(
            'superadmin.audit-logs.show',
            compact('auditLog')
        );
    }
}
