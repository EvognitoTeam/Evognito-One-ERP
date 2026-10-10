<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\Organization;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditSuperAdminActivity
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response =
            $next($request);

        if (
            !$request->user()
            || in_array(
                strtoupper(
                    $request->method()
                ),
                [
                    'GET',
                    'HEAD',
                    'OPTIONS',
                ],
                true
            )
        ) {
            return $response;
        }

        try {
            $this->writeLog(
                $request,
                $response
            );
        } catch (\Throwable $e) {
            /*
             * Audit logging tidak boleh membuat
             * request utama gagal.
             */
            report($e);
        }

        return $response;
    }

    protected function writeLog(
        Request $request,
        Response $response
    ): void {
        $route =
            $request->route();

        $routeName =
            $route?->getName();

        $organizationId =
            $this->resolveOrganizationId(
                $request
            );

        [
            $subjectType,
            $subjectId,
        ] = $this->resolveSubject(
            $request
        );

        AuditLog::create([
            'organization_id' =>
            $organizationId,

            'user_id' =>
            $request->user()?->id,

            'event' =>
            $routeName
                ?: $request->path(),

            'action' =>
            $this->resolveAction(
                $request
            ),

            'subject_type' =>
            $subjectType,

            'subject_id' =>
            $subjectId,

            'description' =>
            $this->description(
                $request,
                $routeName
            ),

            'route' =>
            $routeName,

            'method' =>
            strtoupper(
                $request->method()
            ),

            'ip_address' =>
            $request->ip(),

            'user_agent' =>
            $request->userAgent(),

            'metadata' => [
                'path' =>
                $request->path(),

                'response_status' =>
                $response
                    ->getStatusCode(),

                'input' =>
                $this->sanitize(
                    $request->except([
                        '_token',
                        '_method',
                    ])
                ),
            ],
        ]);
    }

    protected function resolveOrganizationId(
        Request $request
    ): ?int {
        foreach (
            $request->route()?->parameters()
                ?? []
            as $parameter
        ) {
            if (
                $parameter
                instanceof Organization
            ) {
                return $parameter->id;
            }
        }

        if (
            $request->filled(
                'organization_id'
            )
        ) {
            return (int) $request->input(
                'organization_id'
            );
        }

        return null;
    }

    protected function resolveSubject(
        Request $request
    ): array {
        foreach (
            $request->route()?->parameters()
                ?? []
            as $parameter
        ) {
            if (
                $parameter instanceof Model
            ) {
                return [
                    class_basename(
                        $parameter
                    ),

                    $parameter->getKey(),
                ];
            }
        }

        return [
            null,
            null,
        ];
    }

    protected function resolveAction(
        Request $request
    ): string {
        return match (strtoupper(
            $request->method()
        )) {
            'POST' =>
            'create',

            'PUT',
            'PATCH' =>
            'update',

            'DELETE' =>
            'delete',

            default =>
            strtolower(
                $request->method()
            ),
        };
    }

    protected function description(
        Request $request,
        ?string $routeName
    ): string {
        if ($routeName) {
            return 'Superadmin action: '
                . $routeName;
        }

        return 'Superadmin '
            . strtoupper(
                $request->method()
            )
            . ' '
            . $request->path();
    }

    protected function sanitize(
        mixed $value,
        ?string $parentKey = null
    ): mixed {
        if (!is_array($value)) {
            return $value;
        }

        $clean = [];

        foreach (
            $value
            as $key => $item
        ) {
            $normalizedKey =
                strtolower(
                    (string) $key
                );

            if (
                $this->isSensitiveKey(
                    $normalizedKey
                )
            ) {
                $clean[$key] =
                    '[REDACTED]';

                continue;
            }

            if (is_array($item)) {
                $clean[$key] =
                    $this->sanitize(
                        $item,
                        $normalizedKey
                    );

                continue;
            }

            $clean[$key] =
                is_string($item)
                && strlen($item) > 2000
                ? substr(
                    $item,
                    0,
                    2000
                ) . '...'
                : $item;
        }

        return $clean;
    }

    protected function isSensitiveKey(
        string $key
    ): bool {
        $keywords = [
            'password',
            'token',
            'secret',
            'api_key',
            'apikey',
            'private_key',
            'credential',
            'authorization',
            'cookie',
        ];

        foreach (
            $keywords
            as $keyword
        ) {
            if (
                str_contains(
                    $key,
                    $keyword
                )
            ) {
                return true;
            }
        }

        return false;
    }
}
