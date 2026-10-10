<?php

namespace App\Modules\Performance\Http\Controllers;

use App\Core\Imports\Models\MasterDataImport;
use App\Core\Imports\Services\EmployeeMasterDataImportService;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PerformanceMasterDataImportController extends PerformanceController
{
    public function __construct(
        private readonly EmployeeMasterDataImportService $importService
    ) {
    }

    public function index(
        Organization $organization
    ): View {
        $imports = MasterDataImport::query()
            ->where('organization_id', $organization->id)
            ->where('type', 'employee_master')
            ->latest('id')
            ->paginate(20);

        return view(
            'modules.performance.master-data.index',
            compact(
                'organization',
                'imports'
            )
        );
    }

    public function store(
        Request $request,
        Organization $organization
    ): RedirectResponse {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx',
                'max:15360',
            ],
        ]);

        try {
            $import = $this->importService->import(
                $organization,
                $request->file('file'),
                auth()->id()
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => __('performance.import.failed', [
                        'message' => $exception->getMessage(),
                    ]),
                ]);
        }

        return redirect()
            ->route(
                'performance.master-data.show',
                [
                    'organization' => $organization->slug,
                    'import' => $import->id,
                ]
            )
            ->with(
                'success',
                __('performance.import.completed')
            );
    }

    public function show(
        Organization $organization,
        MasterDataImport $import
    ): View {
        abort_unless(
            (int) $import->organization_id
            ===
            (int) $organization->id,
            404
        );

        $rows = $import->rows()
            ->orderBy('source_row_number')
            ->paginate(50);

        return view(
            'modules.performance.master-data.show',
            compact(
                'organization',
                'import',
                'rows'
            )
        );
    }
}
