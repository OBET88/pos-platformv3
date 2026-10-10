<?php

namespace App\Services\Platform;

use App\Models\Platform\PlatformPaymentTransaction;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class DokuCheckoutService
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function createCheckout(PlatformPaymentTransaction $transaction): array
    {
        $config = $this->config();
        $this->guardConfigured($config);

        $requestId = (string) Str::uuid();
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');
        $endpointPath = '/checkout/v1/payment';
        $payload = $this->buildCheckoutPayload($transaction, $config);
        $signature = $this->generateSignature(
            clientId: $config['client_id'],
            requestId: $requestId,
            timestamp: $timestamp,
            requestTarget: $endpointPath,
            body: $payload,
            secretKey: $config['secret_key'],
        );

        $response = $this->http
            ->acceptJson()
            ->withHeaders([
                'Client-Id' => $config['client_id'],
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => $signature,
            ])
            ->post(rtrim($config['base_url'], '/').$endpointPath, $payload);

        $this->ensureSuccessfulResponse($response, $transaction);

        $body = $response->json();
        $paymentUrl = Arr::get($body, 'response.payment.url')
            ?? Arr::get($body, 'payment.url')
            ?? Arr::get($body, 'payment_url');

        if (!is_string($paymentUrl) || trim($paymentUrl) === '') {
            throw new RuntimeException('DOKU Checkout tidak mengembalikan payment URL yang valid.');
        }

        return [
            'request_id' => $requestId,
            'timestamp' => $timestamp,
            'payment_url' => $paymentUrl,
            'response' => $body,
        ];
    }

    public function isEnabled(): bool
    {
        $config = $this->config();

        return (bool) ($config['enabled'] ?? false);
    }

    public function verifyWebhookSignature(string $rawBody, array $headers, string $requestTarget): bool
    {
        $config = $this->config();

        if (($config['secret_key'] ?? '') === '') {
            return false;
        }

        $clientId = $this->headerValue($headers, 'Client-Id');
        $requestId = $this->headerValue($headers, 'Request-Id');
        $timestamp = $this->headerValue($headers, 'Request-Timestamp');
        $providedSignature = $this->headerValue($headers, 'Signature');

        if ($clientId === '' || $requestId === '' || $timestamp === '' || $providedSignature === '') {
            return false;
        }

        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            return false;
        }

        $expectedSignature = $this->generateSignature(
            clientId: $clientId,
            requestId: $requestId,
            timestamp: $timestamp,
            requestTarget: $requestTarget,
            body: $payload,
            secretKey: $config['secret_key'],
        );

        return hash_equals($expectedSignature, $providedSignature);
    }

    public function mapDokuStatus(array $payload): string
    {
        $transactionStatus = Str::upper((string) Arr::get($payload, 'transaction.status', ''));
        $orderStatus = Str::upper((string) Arr::get($payload, 'order.status', ''));

        return match (true) {
            $transactionStatus === 'SUCCESS' => 'paid',
            $orderStatus === 'ORDER_EXPIRED' || $transactionStatus === 'EXPIRED' => 'expired',
            $transactionStatus === 'FAILED' => 'failed',
            $transactionStatus === 'PENDING',
            $orderStatus === 'ORDER_GENERATED',
            $orderStatus === 'ORDER_RECOVERED' => 'pending_payment',
            default => 'pending_payment',
        };
    }

    public function requestTargetForNotification(string $pathInfo): string
    {
        return '/'.ltrim($pathInfo, '/');
    }

    private function buildCheckoutPayload(PlatformPaymentTransaction $transaction, array $config): array
    {
        $callbackUrl = route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number]);
        $paymentDueDate = $this->resolvePaymentDueMinutes($transaction);
        $customerName = $transaction->tenant?->owner_name ?? $transaction->tenant?->name ?? 'Customer';

        $payload = [
            'order' => [
                'amount' => (int) round((float) $transaction->amount_total),
                'invoice_number' => $transaction->invoice_number,
                'currency' => 'IDR',
                'callback_url' => $callbackUrl,
                'callback_url_result' => $callbackUrl,
                'auto_redirect' => true,
                'language' => 'ID',
                'line_items' => [
                    [
                        'name' => 'Langganan '.$transaction->plan?->name,
                        'price' => (int) round((float) $transaction->amount_total),
                        'quantity' => 1,
                    ],
                ],
            ],
            'payment' => [
                'payment_due_date' => $paymentDueDate,
            ],
            'customer' => [
                'name' => $customerName,
                'email' => $transaction->tenant?->owner_email,
                'phone' => $transaction->tenant?->owner_phone,
            ],
            'additional_info' => [
                'origin' => [
                    'product' => 'POS_PLATFORM',
                    'source' => 'platform_payment_transaction',
                ],
                'merchant_notification_url' => route('platform.webhooks.doku.checkout'),
            ],
        ];

        $paymentMethodTypes = array_values(array_filter(array_map(
            static fn ($value) => is_string($value) ? trim($value) : '',
            explode(',', (string) ($config['payment_method_types'] ?? ''))
        )));

        if ($paymentMethodTypes !== []) {
            $payload['payment']['payment_method_types'] = $paymentMethodTypes;
        }

        return $payload;
    }

    private function resolvePaymentDueMinutes(PlatformPaymentTransaction $transaction): int
    {
        if (!$transaction->due_at instanceof Carbon) {
            return 60;
        }

        $minutes = now()->diffInMinutes($transaction->due_at, false);

        return max(1, min(44640, (int) $minutes));
    }

    private function generateSignature(
        string $clientId,
        string $requestId,
        string $timestamp,
        string $requestTarget,
        array $body,
        string $secretKey
    ): string {
        $minifiedBody = json_encode($body, JSON_UNESCAPED_SLASHES);

        if ($minifiedBody === false) {
            throw new RuntimeException('Gagal membuat body JSON untuk signature DOKU.');
        }

        $digest = base64_encode(hash('sha256', $minifiedBody, true));
        $components = implode("\n", [
            'Client-Id:'.$clientId,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:'.$requestTarget,
            'Digest:'.$digest,
        ]);

        return 'HMACSHA256='.base64_encode(hash_hmac('sha256', $components, $secretKey, true));
    }

    private function ensureSuccessfulResponse(Response $response, PlatformPaymentTransaction $transaction): void
    {
        if ($response->successful()) {
            return;
        }

        Log::warning('DOKU checkout request failed.', [
            'invoice_number' => $transaction->invoice_number,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new RuntimeException('Permintaan DOKU Checkout gagal. Status: '.$response->status());
    }

    private function guardConfigured(array $config): void
    {
        if (!(bool) ($config['enabled'] ?? false)) {
            throw new RuntimeException('Integrasi DOKU belum diaktifkan di konfigurasi.');
        }

        foreach (['client_id', 'secret_key', 'base_url'] as $key) {
            if (!is_string($config[$key] ?? null) || trim((string) $config[$key]) === '') {
                throw new RuntimeException('Konfigurasi DOKU belum lengkap untuk field `'.$key.'`.');
            }
        }
    }

    private function headerValue(array $headers, string $key): string
    {
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
        $value = $normalizedHeaders[strtolower($key)] ?? '';

        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        return is_string($value) ? trim($value) : '';
    }

    private function config(): array
    {
        /** @var array $config */
        $config = config('services.doku', []);

        return $config;
    }
}
