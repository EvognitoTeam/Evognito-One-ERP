<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\PlatformSetting;
use App\Services\InvoiceService;
use App\Services\ManualInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected ManualInvoiceService $manualInvoiceService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Invoice Directory
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): View {
        $this->invoiceService
            ->syncOverdue();

        $search =
            trim(
                (string) $request->get(
                    'search'
                )
            );

        $status =
            trim(
                (string) $request->get(
                    'status'
                )
            );

        $source =
            trim(
                (string) $request->get(
                    'source'
                )
            );

        $invoices =
            Invoice::query()
            ->with([
                'organization',
                'subscription.plan',
                'creator',
            ])

            ->when(
                $search !== '',
                function (
                    $query
                ) use (
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
                                    'invoice_number',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'title',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'organization',
                                    fn(
                                        $organizationQuery
                                    ) =>
                                    $organizationQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    $status
                )
            )

            ->when(
                $source !== '',
                fn($query) =>
                $query->where(
                    'source',
                    $source
                )
            )

            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'superadmin.invoices.index',
            compact(
                'invoices',
                'search',
                'status',
                'source'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription Invoice
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $subscriptions =
            OrganizationSubscription::query()
            ->with([
                'organization',
                'plan',
            ])
            ->whereIn(
                'status',
                [
                    'active',
                    'trialing',
                    'past_due',
                ]
            )
            ->latest()
            ->get();

        return view(
            'superadmin.invoices.create',
            compact(
                'subscriptions'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'subscription_id' => [
                    'required',
                    'integer',
                    'exists:organization_subscriptions,id',
                ],

                'issue_now' => [
                    'nullable',
                    'boolean',
                ],
            ]);

        $subscription =
            OrganizationSubscription::findOrFail(
                $validated['subscription_id']
            );

        $invoice =
            $this
            ->invoiceService
            ->generate(
                $subscription,
                $request->boolean(
                    'issue_now'
                )
            );

        return redirect()
            ->route(
                'superadmin.invoices.show',
                $invoice
            )
            ->with(
                'success',
                'Subscription invoice berhasil dibuat.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Manual Invoice
    |--------------------------------------------------------------------------
    */

    public function createManual(): View
    {
        $organizations =
            Organization::query()
            ->where(
                'status',
                'active'
            )
            ->orderBy('name')
            ->get();

        $defaultTaxPercent =
            (float) PlatformSetting::getValue(
                'billing.default_tax_percent',
                0
            );

        $invoiceDueDays =
            (int) PlatformSetting::getValue(
                'billing.invoice_due_days',
                7
            );

        return view(
            'superadmin.invoices.manual-create',
            compact(
                'organizations',
                'defaultTaxPercent',
                'invoiceDueDays'
            )
        );
    }

    public function storeManual(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'organization_id' => [
                    'required',
                    'integer',
                    'exists:organizations,id',
                ],

                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'period_start' => [
                    'nullable',
                    'date',
                ],

                'period_end' => [
                    'nullable',
                    'date',
                    'after_or_equal:period_start',
                ],

                'due_at' => [
                    'nullable',
                    'date',
                ],

                'discount_total' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'tax_percent' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'issue_now' => [
                    'nullable',
                    'boolean',
                ],

                'items' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'items.*.code' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'items.*.description' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'items.*.quantity' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],

                'items.*.unit_price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
            ]);

        $organization =
            Organization::findOrFail(
                $validated['organization_id']
            );

        $validated['issue_now'] =
            $request->boolean(
                'issue_now'
            );

        $invoice =
            $this
            ->manualInvoiceService
            ->create(
                $organization,
                $request->user(),
                $validated
            );

        return redirect()
            ->route(
                'superadmin.invoices.show',
                $invoice
            )
            ->with(
                'success',
                'Custom invoice berhasil dibuat.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Detail
    |--------------------------------------------------------------------------
    */

    public function show(
        Invoice $invoice
    ): View {
        $invoice->load([
            'organization',
            'subscription.plan',
            'creator',
            'items.module',
            'payments',
        ]);

        return view(
            'superadmin.invoices.show',
            compact('invoice')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Issue
    |--------------------------------------------------------------------------
    */

    public function issue(
        Invoice $invoice
    ): RedirectResponse {
        $this
            ->invoiceService
            ->issue(
                $invoice
            );

        return back()->with(
            'success',
            'Invoice berhasil diterbitkan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Invoice $invoice
    ): RedirectResponse {
        $this
            ->invoiceService
            ->cancel(
                $invoice
            );

        return back()->with(
            'success',
            'Invoice berhasil dibatalkan.'
        );
    }
}
