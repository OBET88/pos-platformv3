<?php

namespace App\Support\Billing;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Kalkulator langganan (renewal/upgrade) — satu sumber kebenaran.
 *
 * Prinsip:
 * - Pelanggan TIDAK kehilangan hari yang sudah dibayar.
 * - Pelanggan TIDAK membayar lebih dari harga paket barunya.
 *
 * Renewal  : bayar harga paket × siklus; masa aktif ditambah dari tanggal
 *            berakhir yang ada (atau hari ini bila sudah lewat).
 * Upgrade  : pindah paket sekarang; sisa hari paket lama dihitung sebagai
 *            POTONGAN (prorata); siklus baru mulai dari hari pembayaran.
 */
class SubscriptionBilling
{
    /** Pembulatan potongan ke rupiah terdekat agar invoice rapi. */
    public const CREDIT_ROUNDING = 1000;

    public static function cycleMonths(string $billingCycle): int
    {
        return match (strtolower(trim($billingCycle))) {
            'quarterly' => 3,
            'yearly', 'annual' => 12,
            default => 1,
        };
    }

    public static function cycleDays(string $billingCycle): int
    {
        return match (strtolower(trim($billingCycle))) {
            'quarterly' => 90,
            'yearly', 'annual' => 365,
            default => 30,
        };
    }

    public static function cycleLabel(string $billingCycle): string
    {
        return match (strtolower(trim($billingCycle))) {
            'quarterly' => '3 bulan',
            'yearly', 'annual' => '12 bulan',
            default => '1 bulan',
        };
    }

    /**
     * Tanggal berakhir hasil PERPANJANGAN (masa aktif ditambah).
     */
    public static function renewalExpiresAt(?CarbonInterface $currentExpiry, CarbonInterface $paidAt, string $billingCycle): Carbon
    {
        $base = ($currentExpiry && $currentExpiry->greaterThan($paidAt))
            ? Carbon::parse($currentExpiry)
            : Carbon::parse($paidAt);

        return $base->copy()->addMonthsNoOverflow(self::cycleMonths($billingCycle));
    }

    /**
     * Tanggal berakhir hasil UPGRADE/DOWNGRADE (siklus baru dari hari bayar).
     */
    public static function changeExpiresAt(CarbonInterface $paidAt, string $billingCycle): Carbon
    {
        return Carbon::parse($paidAt)->copy()->addMonthsNoOverflow(self::cycleMonths($billingCycle));
    }

    /**
     * Sisa hari pada masa aktif (0 bila sudah lewat / tanpa tanggal).
     */
    public static function remainingDays(?CarbonInterface $currentExpiry, CarbonInterface $now): int
    {
        if (! $currentExpiry) {
            return 0;
        }

        // Bandingkan pada granularitas tanggal agar deterministik (mis. "sisa 15 hari").
        return max(0, (int) Carbon::parse($now)->startOfDay()
            ->diffInDays(Carbon::parse($currentExpiry)->startOfDay(), false));
    }

    /**
     * Potongan prorata dari sisa hari paket lama.
     */
    public static function upgradeCredit(
        float $currentPlanPrice,
        ?CarbonInterface $currentExpiry,
        CarbonInterface $now,
        string $billingCycle,
        ?float $cap = null
    ): float {
        $remaining = self::remainingDays($currentExpiry, $now);

        if ($remaining <= 0 || $currentPlanPrice <= 0) {
            return 0.0;
        }

        $cycleDays = max(1, self::cycleDays($billingCycle));
        $credit = $currentPlanPrice * ($remaining / $cycleDays);
        $credit = round($credit / self::CREDIT_ROUNDING) * self::CREDIT_ROUNDING;

        if ($cap !== null) {
            $credit = min($credit, $cap);
        }

        return max(0.0, (float) $credit);
    }

    /**
     * Perhitungan upgrade: potongan + biaya akhir.
     *
     * @return array{credit: float, amount: float, target: float}
     */
    public static function upgradeAmount(
        float $currentPlanPrice,
        float $targetPlanPrice,
        ?CarbonInterface $currentExpiry,
        CarbonInterface $now,
        string $billingCycle
    ): array {
        $multiplier = self::cycleMonths($billingCycle);
        $target = max(0.0, $targetPlanPrice * $multiplier);

        $credit = self::upgradeCredit($currentPlanPrice, $currentExpiry, $now, $billingCycle, $target);

        return [
            'credit' => (float) $credit,
            'amount' => max(0.0, $target - $credit),
            'target' => $target,
        ];
    }
}
