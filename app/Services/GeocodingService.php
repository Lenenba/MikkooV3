<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    public function search(string $query, int $limit = 6): array
    {
        $apiKey = config('services.geoapify.key');
        if (! $apiKey) {
            return [];
        }

        try {
            $response = Http::get('https://api.geoapify.com/v1/geocode/autocomplete', [
                'text' => $query,
                'limit' => $limit,
                'lang' => app()->getLocale(),
                'apiKey' => $apiKey,
            ]);
        } catch (\Throwable $exception) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $payload = $response->json();
        $features = $payload['features'] ?? null;
        if (! is_array($features)) {
            return [];
        }

        return collect($features)
            ->map(function (array $feature) {
                $properties = $feature['properties'] ?? [];
                $coordinates = $feature['geometry']['coordinates'] ?? null;
                return $this->mapResult($properties, $coordinates);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function mapResult(array $properties, ?array $coordinates): ?array
    {
        $streetParts = array_filter([
            $properties['housenumber'] ?? null,
            $properties['street'] ?? null,
        ]);
        $street = trim(implode(' ', $streetParts));

        $city = $properties['city']
            ?? $properties['town']
            ?? $properties['village']
            ?? $properties['municipality']
            ?? null;
        $province = $properties['state'] ?? $properties['region'] ?? null;
        $postalCode = $properties['postcode'] ?? null;
        $country = $properties['country'] ?? null;

        $lat = $properties['lat'] ?? ($coordinates[1] ?? null);
        $lng = $properties['lon'] ?? ($coordinates[0] ?? null);

        if (! $city && ! $country) {
            return null;
        }

        return [
            'display_name' => $properties['formatted']
                ?? trim($street . ' ' . ($city ?? '') . ' ' . ($country ?? '')),
            'street' => $street,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postalCode,
            'country' => $country,
            'latitude' => $lat !== null ? (float) $lat : null,
            'longitude' => $lng !== null ? (float) $lng : null,
        ];
    }
}
