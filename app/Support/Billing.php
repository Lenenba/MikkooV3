<?php

namespace App\Support;

class Billing
{
    public static function vatRateForCountry(?string $country): float
    {
        $rates = (array) config('billing.vat_rates', []);
        $normalized = self::normalizeCountry($country);

        if ($normalized !== null) {
            foreach ($rates as $key => $rate) {
                if (strcasecmp($key, $normalized) === 0) {
                    return (float) $rate;
                }
            }
        }

        return (float) config('billing.default_vat_rate', 0);
    }

    public static function currencyForCountry(?string $country): string
    {
        $currencies = (array) config('billing.currency_by_country', []);
        $normalized = self::normalizeCountry($country);

        if ($normalized !== null) {
            foreach ($currencies as $key => $currency) {
                if (strcasecmp($key, $normalized) === 0) {
                    return (string) $currency;
                }
            }
        }

        return (string) config('billing.default_currency', 'USD');
    }

    private static function normalizeCountry(?string $country): ?string
    {
        $value = trim((string) ($country ?? ''));
        return $value !== '' ? $value : null;
    }
}
