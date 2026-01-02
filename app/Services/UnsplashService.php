<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UnsplashService
{
    public function random(string $query, int $count = 1, array $options = []): array
    {
        $apiKey = config('services.unsplash.key');
        if (! $apiKey) {
            return [];
        }

        $params = array_merge([
            'query' => $query,
            'count' => $count,
            'content_filter' => 'high',
        ], $options);

        try {
            $response = Http::withHeaders([
                'Accept-Version' => 'v1',
            ])->get('https://api.unsplash.com/photos/random', array_merge($params, [
                'client_id' => $apiKey,
            ]));
        } catch (\Throwable $exception) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $payload = $response->json();
        $items = $count === 1 ? [$payload] : (is_array($payload) ? $payload : []);

        return collect($items)
            ->map(fn($item) => $this->mapItem($item))
            ->filter()
            ->values()
            ->all();
    }

    private function mapItem(?array $item): ?array
    {
        if (! is_array($item)) {
            return null;
        }

        $urls = $item['urls'] ?? [];
        $url = $urls['regular'] ?? $urls['full'] ?? $urls['small'] ?? null;
        if (! $url) {
            return null;
        }

        return [
            'id' => $item['id'] ?? null,
            'url' => $url,
            'alt' => $item['alt_description'] ?? $item['description'] ?? 'Unsplash photo',
            'user' => $item['user']['name'] ?? null,
            'link' => $item['links']['html'] ?? null,
        ];
    }
}
