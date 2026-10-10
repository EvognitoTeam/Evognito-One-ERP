<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(
        Request $request
    ): View {
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

        $payments =
            Payment::query()
            ->with([
                'organization',
                'invoice',
            ])

            ->when(
                $search !== '',
                function ($query) use (
                    $search
                ) {
                    $query->where(
                        function ($subQuery) use (
                            $search
                        ) {
                            $subQuery
                                ->where(
                                    'payment_number',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'reference',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'organization',
                                    fn($organizationQuery) =>
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

            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'superadmin.payments.index',
            compact(
                'payments',
                'search',
                'status'
            )
        );
    }

    public function create(
        Request $request
    ): View {
        $invoiceId =
            $request->integer(
                'invoice'
            );

        $invoices =
            Invoice::query()
            ->with(
                'organization'
            )
            ->whereIn(
                'status',
                [
                    'issued',
                    'partial',
                    'overdue',
                ]
            )
            ->orderByDesc(
                'issued_at'
            )
            ->get();

        return view(
            'superadmin.payments.create',
            compact(
                'invoices',
                'invoiceId'
            )
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'invoice_id' => [
                    'required',
                    'integer',
                    'exists:invoices,id',
                ],

                'method' => [
                    'required',

                    Rule::in([
                        'bank_transfer',
                        'cash',
                        'virtual_account',
                        'other',
                    ]),
                ],

                'status' => [
                    'required',

                    Rule::in([
                        'pending',
                        'paid',
                    ]),
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'paid_at' => [
                    'nullable',
                    'date',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ]);

        $invoice =
            Invoice::findOrFail(
                $validated['invoice_id']
            );

        $payment =
            $this->paymentService
            ->record(
                $invoice,
                [
                    'provider' =>
                    'manual',

                    'method' =>
                    $validated['method'],

                    'status' =>
                    $validated['status'],

                    'amount' =>
                    $validated['amount'],

                    'reference' =>
                    $validated['reference']
                        ?? null,

                    'paid_at' =>
                    !empty($validated['paid_at'])
                        ? Carbon::parse(
                            $validated['paid_at']
                        )
                        : now(),

                    'notes' =>
                    $validated['notes']
                        ?? null,
                ]
            );

        return redirect()
            ->route(
                'superadmin.payments.show',
                $payment
            )
            ->with(
                'success',
                'Payment berhasil dicatat.'
            );
    }

    public function show(
        Payment $payment
    ): View {
        $payment->load([
            'organization',
            'invoice',
        ]);

        return view(
            'superadmin.payments.show',
            compact('payment')
        );
    }

    public function cancel(
        Payment $payment
    ): RedirectResponse {
        $this->paymentService
            ->cancel(
                $payment
            );

        return back()->with(
            'success',
            'Payment berhasil dibatalkan.'
        );
    }
}
