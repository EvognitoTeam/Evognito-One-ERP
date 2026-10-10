<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function record(
        Invoice $invoice,
        array $data
    ): Payment {
        if (
            in_array(
                $invoice->status,
                [
                    'draft',
                    'canceled',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'invoice' =>
                'Invoice ini belum dapat menerima payment.',
            ]);
        }

        $amount =
            (float) $data['amount'];

        if (
            $amount
            > $invoice->outstanding_amount
        ) {
            throw ValidationException::withMessages([
                'amount' =>
                'Nominal payment melebihi outstanding invoice.',
            ]);
        }

        return DB::transaction(
            function () use (
                $invoice,
                $data,
                $amount
            ) {
                $status =
                    $data['status']
                    ?? 'paid';

                $payment =
                    Payment::create([
                        'organization_id' =>
                        $invoice
                            ->organization_id,

                        'invoice_id' =>
                        $invoice->id,

                        'provider' =>
                        $data['provider']
                            ?? 'manual',

                        'method' =>
                        $data['method']
                            ?? 'bank_transfer',

                        'status' =>
                        $status,

                        'amount' =>
                        $amount,

                        'currency' =>
                        $invoice->currency
                            ?: 'IDR',

                        'reference' =>
                        $data['reference']
                            ?? null,

                        'provider_reference' =>
                        $data['provider_reference']
                            ?? null,

                        'paid_at' =>
                        $status === 'paid'
                            ? (
                                $data['paid_at']
                                ?? now()
                            )
                            : null,

                        'notes' =>
                        $data['notes']
                            ?? null,

                        'metadata' =>
                        $data['metadata']
                            ?? null,
                    ]);

                $this->syncInvoice(
                    $invoice
                );

                return $payment;
            }
        );
    }

    public function cancel(
        Payment $payment
    ): Payment {
        if (
            $payment->status
            === 'canceled'
        ) {
            return $payment;
        }

        return DB::transaction(
            function () use (
                $payment
            ) {
                $payment->update([
                    'status' =>
                    'canceled',
                ]);

                if ($payment->invoice) {
                    $this->syncInvoice(
                        $payment->invoice
                    );
                }

                return $payment->refresh();
            }
        );
    }

    public function syncInvoice(
        Invoice $invoice
    ): Invoice {
        $invoice->refresh();

        if (
            $invoice->status
            === 'canceled'
        ) {
            return $invoice;
        }

        $paid =
            (float) $invoice
                ->payments()
                ->where(
                    'status',
                    'paid'
                )
                ->sum('amount');

        $total =
            (float) $invoice->total;

        if (
            $total > 0
            && $paid >= $total
        ) {
            $invoice->update([
                'status' =>
                'paid',

                'paid_at' =>
                $invoice->paid_at
                    ?: now(),
            ]);

            return $invoice->refresh();
        }

        if ($paid > 0) {
            $invoice->update([
                'status' => (
                    $invoice->due_at
                    && $invoice->due_at->isPast()
                )
                    ? 'overdue'
                    : 'partial',

                'paid_at' =>
                null,
            ]);

            return $invoice->refresh();
        }

        $invoice->update([
            'status' => (
                $invoice->due_at
                && $invoice->due_at->isPast()
            )
                ? 'overdue'
                : (
                    $invoice->issued_at
                    ? 'issued'
                    : 'draft'
                ),

            'paid_at' =>
            null,
        ]);

        return $invoice->refresh();
    }
}
