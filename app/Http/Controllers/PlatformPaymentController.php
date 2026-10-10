<?php

namespace App\Http\Controllers;

use App\Models\Platform\PlatformPaymentProof;
use App\Models\Platform\PlatformPaymentEvent;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Services\Platform\DokuCheckoutService;
use App\Services\Platform\PaymentActivationService;
use App\Services\Platform\PlatformInboxPublisher;
use App\Services\Platform\PlatformSettings;
use App\Services\Platform\TenantAuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PlatformPaymentController extends Controller
{
    public function show(string $invoiceNumber, PlatformSettings $platformSettings): View
    {
        $transaction = $this->resolveTransaction($invoiceNumber);

        return view('platform.payment-manual', [
            'transaction' => $transaction,
            'dokuEnabled' => app(DokuCheckoutService::class)->isEnabled(),
            'paymentDestination' => $platformSettings->paymentDestination(),
        ]);
    }

    public function showPdf(string $invoiceNumber, PlatformSettings $platformSettings): View
    {
        $transaction = $this->resolveTransaction($invoiceNumber);

        return view('platform.payment-pdf', [
            'transaction' => $transaction,
            'paymentDestination' => $platformSettings->paymentDestination(),
        ]);
    }

    public function redirectToDoku(
        string $invoiceNumber,
        DokuCheckoutService $dokuCheckoutService
    ): RedirectResponse {
        $transaction = $this->resolveTransaction($invoiceNumber);

        if ($transaction->status === 'paid') {
            return redirect()
                ->route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number])
                ->withErrors([
                    'submitted_by_name' => 'Invoice ini sudah lunas dan tidak perlu dibayar ulang lewat DOKU.',
                ]);
        }

        try {
            $checkout = $dokuCheckoutService->createCheckout($transaction);

            $transaction->forceFill([
                'payment_method' => 'gateway',
                'payment_provider' => 'doku',
                'provider_reference' => $checkout['request_id'],
                'status' => 'pending_payment',
                'meta_json' => array_filter([
                    ...($transaction->meta_json ?? []),
                    'doku_checkout_url' => $checkout['payment_url'],
                    'doku_request_id' => $checkout['request_id'],
                    'doku_response' => $checkout['response'],
                ], fn ($value) => $value !== null && $value !== ''),
            ])->save();

            return redirect()->away($checkout['payment_url']);
        } catch (\Throwable $exception) {
            Log::warning('Failed to redirect payment transaction to DOKU checkout.', [
                'invoice_number' => $transaction->invoice_number,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number])
                ->withErrors([
                    'submitted_by_name' => 'Checkout DOKU belum berhasil dibuat: '.$exception->getMessage(),
                ]);
        }
    }

    public function submitProof(
        Request $request,
        string $invoiceNumber,
        TenantAuditLogger $auditLogger,
        PlatformInboxPublisher $inboxPublisher
    ): RedirectResponse {
        $transaction = $this->resolveTransaction($invoiceNumber);

        if (in_array($transaction->status, ['paid', 'cancelled', 'refunded', 'expired'], true)) {
            return back()->withErrors([
                'submitted_by_name' => 'Invoice ini sudah tidak menerima bukti pembayaran baru.',
            ]);
        }

        $validated = $request->validate([
            'submitted_by_name' => ['required', 'string', 'max:255'],
            'submitted_by_email' => ['required', 'email', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number_masked' => ['nullable', 'string', 'max:50'],
            'transfer_amount' => ['required', 'numeric', 'min:0'],
            'transfer_date' => ['required', 'date'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        DB::transaction(function () use ($validated, $request, $transaction, $auditLogger, $inboxPublisher): void {
            $proofPath = $request->file('proof_file')?->store('payment-proofs', 'public');

            PlatformPaymentProof::query()->create([
                'payment_transaction_id' => $transaction->getKey(),
                'bank_name' => $validated['bank_name'] ?? null,
                'account_name' => $validated['account_name'] ?? null,
                'account_number_masked' => $validated['account_number_masked'] ?? null,
                'transfer_amount' => (float) $validated['transfer_amount'],
                'transfer_date' => $validated['transfer_date'],
                'proof_file_path' => $proofPath,
                'submitted_by_name' => $validated['submitted_by_name'],
                'submitted_by_email' => $validated['submitted_by_email'],
                'review_status' => 'pending',
            ]);

            $beforeStatus = $transaction->status;

            $transaction->forceFill([
                'status' => 'waiting_validation',
                'meta_json' => array_filter([
                    ...($transaction->meta_json ?? []),
                    'latest_proof_submitted_at' => now()->toISOString(),
                    'latest_proof_submitted_by' => $validated['submitted_by_email'],
                ], fn ($value) => $value !== null && $value !== ''),
            ])->save();

            $auditLogger->logSystem(
                $transaction->tenant,
                'tenant.payment.proof_submitted',
                'Bukti pembayaran dikirim untuk invoice '.$transaction->invoice_number.'.',
                [
                    'before' => [
                        'status' => $beforeStatus,
                    ],
                    'after' => [
                        'status' => $transaction->status,
                        'invoice_number' => $transaction->invoice_number,
                        'submitted_by_email' => $validated['submitted_by_email'],
                    ],
                ],
                [
                    'stage' => 'payment-proof-'.$transaction->getKey(),
                ]
            );

            $inboxPublisher->publish(
                tenant: $transaction->tenant,
                eventKey: 'tenant.payment.awaiting_validation',
                title: 'Menunggu validasi pembayaran',
                message: 'Invoice '.$transaction->invoice_number.' menunggu validasi pembayaran dari admin.',
                category: 'renewal',
                tone: 'amber',
                payload: [
                    'payment_transaction_id' => $transaction->getKey(),
                    'invoice_number' => $transaction->invoice_number,
                    'submitted_by_email' => $validated['submitted_by_email'],
                ],
                occurredAt: now(),
                fingerprint: 'payment.awaiting_validation:'.$transaction->getKey().':'.now()->format('YmdHis'),
                sourceType: PlatformPaymentTransaction::class,
                sourceId: $transaction->getKey(),
            );
        });

        return redirect()
            ->route('platform.orders.index', [
                'tenant' => $transaction->tenant?->code,
                'owner_email' => $transaction->tenant?->owner_email ?: $validated['submitted_by_email'],
                'invoice' => $transaction->invoice_number,
            ])
            ->with('payment_proof_submitted', true)
            ->with('payment_invoice_number', $transaction->invoice_number);
    }

    public function dokuWebhook(
        Request $request,
        DokuCheckoutService $dokuCheckoutService,
        PaymentActivationService $paymentActivationService
    ): JsonResponse {
        $payload = (array) $request->json()->all();
        $invoiceNumber = (string) Arr::get($payload, 'order.invoice_number', '');

        if ($invoiceNumber === '') {
            return response()->json([
                'status' => 'ignored',
                'message' => 'Invoice number tidak ditemukan pada payload.',
            ], 422);
        }

        $transaction = PlatformPaymentTransaction::query()
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'ignored',
                'message' => 'Invoice lokal tidak ditemukan.',
            ], 404);
        }

        $signatureValid = $dokuCheckoutService->verifyWebhookSignature(
            rawBody: (string) $request->getContent(),
            headers: $request->headers->all(),
            requestTarget: $dokuCheckoutService->requestTargetForNotification($request->getPathInfo()),
        );

        $mappedStatus = $dokuCheckoutService->mapDokuStatus($payload);

        $event = PlatformPaymentEvent::query()->create([
            'payment_transaction_id' => $transaction->getKey(),
            'payment_provider' => 'doku',
            'event_key' => (string) Arr::get($payload, 'transaction.status', Arr::get($payload, 'order.status', 'notification')),
            'provider_reference' => (string) Arr::get($payload, 'transaction.original_request_id', ''),
            'signature_valid' => $signatureValid,
            'payload_json' => $payload,
            'process_status' => 'received',
            'received_at' => now(),
        ]);

        try {
            if ($mappedStatus === 'paid') {
                $paymentActivationService->markAsPaid($transaction, now(), [
                    'approved_via' => 'doku_webhook',
                    'signature_valid' => $signatureValid,
                ]);
            } else {
                $transaction->forceFill([
                    'payment_method' => 'gateway',
                    'payment_provider' => 'doku',
                    'provider_reference' => (string) Arr::get($payload, 'transaction.original_request_id', $transaction->provider_reference),
                    'status' => $mappedStatus,
                    'meta_json' => array_filter([
                        ...($transaction->meta_json ?? []),
                        'doku_last_webhook_at' => now()->toISOString(),
                        'doku_last_payload' => $payload,
                    ], fn ($value) => $value !== null && $value !== ''),
                ])->save();
            }

            $event->forceFill([
                'process_status' => 'processed',
                'processed_at' => now(),
            ])->save();

            return response()->json([
                'status' => 'processed',
                'invoice_number' => $invoiceNumber,
            ]);
        } catch (\Throwable $exception) {
            $event->forceFill([
                'process_status' => 'failed',
                'processed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ])->save();

            Log::warning('Failed to process DOKU webhook.', [
                'invoice_number' => $invoiceNumber,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function resolveTransaction(string $invoiceNumber): PlatformPaymentTransaction
    {
        return PlatformPaymentTransaction::query()
            ->with(['tenant', 'plan', 'proofs' => fn ($query) => $query->latest('id')])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();
    }
}
