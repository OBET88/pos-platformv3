<?php

namespace App\Services\Platform;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformSetting;
use Illuminate\Support\Collection;

class PlatformSettings
{
    private const DEFAULT_TRIAL_DAYS_KEY = 'default_trial_days';
    private const FALLBACK_DEFAULT_TRIAL_DAYS = 14;
    private const PUBLIC_REGISTRATION_PLAN_CODES_KEY = 'public_registration_plan_codes';
    private const PUBLIC_REGISTRATION_DEFAULT_PLAN_CODE_KEY = 'public_registration_default_plan_code';
    private const PAYMENT_BANK_NAME_KEY = 'payment_bank_name';
    private const PAYMENT_ACCOUNT_NAME_KEY = 'payment_account_name';
    private const PAYMENT_ACCOUNT_NUMBER_KEY = 'payment_account_number';
    private const PAYMENT_BRANCH_KEY = 'payment_branch';
    private const PAYMENT_INSTRUCTIONS_KEY = 'payment_manual_instructions';

    public function defaultTrialDays(): int
    {
        $value = PlatformSetting::query()
            ->where('key', self::DEFAULT_TRIAL_DAYS_KEY)
            ->value('value');

        $resolved = is_numeric($value) ? (int) $value : self::FALLBACK_DEFAULT_TRIAL_DAYS;

        return max(1, min(90, $resolved));
    }

    public function setDefaultTrialDays(int $days): int
    {
        $days = max(1, min(90, $days));

        PlatformSetting::query()->updateOrCreate(
            ['key' => self::DEFAULT_TRIAL_DAYS_KEY],
            ['value' => (string) $days]
        );

        return $days;
    }

    public function publicRegistrationPlans(): Collection
    {
        $allowedCodes = $this->publicRegistrationPlanCodes();

        return $this->orderedActivePlansQuery()
            ->when(
                !empty($allowedCodes),
                fn ($query) => $query->whereIn('code', $allowedCodes)
            )
            ->get();
    }

    public function publicRegistrationPlanCodes(): array
    {
        $raw = PlatformSetting::query()
            ->where('key', self::PUBLIC_REGISTRATION_PLAN_CODES_KEY)
            ->value('value');

        $decoded = json_decode(is_string($raw) ? $raw : '[]', true);
        $storedCodes = collect(is_array($decoded) ? $decoded : [])
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn (string $code) => trim($code))
            ->unique()
            ->values()
            ->all();

        $activeCodes = $this->orderedActivePlansQuery()
            ->pluck('code')
            ->all();

        $filteredCodes = array_values(array_intersect($storedCodes, $activeCodes));

        return !empty($filteredCodes) ? $filteredCodes : $activeCodes;
    }

    public function setPublicRegistrationPlanCodes(array $codes): array
    {
        $activeCodes = $this->orderedActivePlansQuery()
            ->pluck('code')
            ->all();

        $resolvedCodes = collect($codes)
            ->filter(fn ($code) => is_string($code) && trim($code) !== '')
            ->map(fn (string $code) => trim($code))
            ->filter(fn (string $code) => in_array($code, $activeCodes, true))
            ->unique()
            ->values()
            ->all();

        if (empty($resolvedCodes)) {
            $resolvedCodes = $activeCodes;
        }

        PlatformSetting::query()->updateOrCreate(
            ['key' => self::PUBLIC_REGISTRATION_PLAN_CODES_KEY],
            ['value' => json_encode($resolvedCodes, JSON_UNESCAPED_UNICODE)]
        );

        $currentDefaultCode = $this->publicRegistrationDefaultPlanCode();

        if (!in_array($currentDefaultCode, $resolvedCodes, true)) {
            $this->setPublicRegistrationDefaultPlanCode($resolvedCodes[0] ?? null, $resolvedCodes);
        }

        return $resolvedCodes;
    }

    public function publicRegistrationDefaultPlanCode(): ?string
    {
        $allowedCodes = $this->publicRegistrationPlanCodes();
        $storedValue = PlatformSetting::query()
            ->where('key', self::PUBLIC_REGISTRATION_DEFAULT_PLAN_CODE_KEY)
            ->value('value');

        $storedCode = is_string($storedValue) ? trim($storedValue) : '';

        if ($storedCode !== '' && in_array($storedCode, $allowedCodes, true)) {
            return $storedCode;
        }

        return $allowedCodes[0] ?? null;
    }

    public function setPublicRegistrationDefaultPlanCode(?string $code, ?array $allowedCodes = null): ?string
    {
        $allowedCodes ??= $this->publicRegistrationPlanCodes();
        $resolvedCode = is_string($code) ? trim($code) : '';

        if ($resolvedCode === '' || !in_array($resolvedCode, $allowedCodes, true)) {
            $resolvedCode = $allowedCodes[0] ?? '';
        }

        PlatformSetting::query()->updateOrCreate(
            ['key' => self::PUBLIC_REGISTRATION_DEFAULT_PLAN_CODE_KEY],
            ['value' => $resolvedCode]
        );

        return $resolvedCode !== '' ? $resolvedCode : null;
    }

    public function paymentDestination(): array
    {
        return [
            'bank_name' => $this->stringSetting(self::PAYMENT_BANK_NAME_KEY, 'BCA'),
            'account_name' => $this->stringSetting(self::PAYMENT_ACCOUNT_NAME_KEY, 'RWP POS INDONESIA'),
            'account_number' => $this->stringSetting(self::PAYMENT_ACCOUNT_NUMBER_KEY, '1234567890'),
            'branch' => $this->stringSetting(self::PAYMENT_BRANCH_KEY, 'Cabang Utama'),
            'instructions' => $this->stringSetting(
                self::PAYMENT_INSTRUCTIONS_KEY,
                'Transfer sesuai nominal invoice, lalu unggah bukti pembayaran yang jelas agar tim admin bisa memvalidasi lebih cepat.'
            ),
        ];
    }

    private function stringSetting(string $key, string $fallback): string
    {
        $value = PlatformSetting::query()
            ->where('key', $key)
            ->value('value');

        $resolved = is_string($value) ? trim($value) : '';

        return $resolved !== '' ? $resolved : $fallback;
    }

    private function orderedActivePlansQuery()
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderByRaw("case when code = 'basic' then 0 when code = 'pro' then 1 when code = 'plus_plus' then 2 when code = 'plus' then 3 when code = 'trial' then 4 when code = 'enterprise' then 5 else 6 end")
            ->orderBy('price');
    }
}
