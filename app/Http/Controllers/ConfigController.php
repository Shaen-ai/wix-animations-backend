<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $config = Config::first();
        if (!$config) {
            return response()->json([]);
        }
        return response()->json($config->toApiArray());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->saveConfig($request->all() ?: [], 201);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->saveConfig($request->all() ?: []);
    }

    public function showAnimation(): JsonResponse
    {
        $config = Config::first();
        if (!$config) {
            return response()->json([
                'enabled' => true,
                'type' => 'floating-hearts',
                'direction' => 'bottom-to-top',
                'scope' => 'all',
                'count' => 14,
            ]);
        }
        $animation = $config->animation ?? [];
        return response()->json(array_merge([
            'enabled' => true,
            'type' => 'floating-hearts',
            'direction' => 'bottom-to-top',
            'scope' => 'all',
            'count' => 14,
        ], $animation));
    }

    public function updateAnimation(Request $request): JsonResponse
    {
        $payload = $request->all() ?: [];
        return $this->saveConfig(['animation' => $payload], 200, true);
    }

    public function showDecorations(): JsonResponse
    {
        $config = Config::first();
        if (!$config) {
            return response()->json(['enabled' => true, 'scope' => 'all']);
        }
        $decorations = $config->decorations ?? [];
        return response()->json(array_merge(['enabled' => true, 'scope' => 'all'], $decorations));
    }

    public function updateDecorations(Request $request): JsonResponse
    {
        $payload = $request->all() ?: [];
        return $this->saveConfig(['decorations' => $payload], 200, true);
    }

    public function showBanners(): JsonResponse
    {
        $config = Config::first();
        if (!$config) {
            return response()->json([]);
        }
        return response()->json($config->banners ?? []);
    }

    public function updateBanners(Request $request): JsonResponse
    {
        $payload = $request->all();
        $banners = is_array($payload) ? $payload : ($payload['banners'] ?? []);
        return $this->saveConfig(['banners' => $banners], 200, true);
    }

    private function saveConfig(array $payload, int $createdStatus = 200, bool $merge = false): JsonResponse
    {
        $data = $merge ? [] : [
            'animation' => $payload['animation'] ?? null,
            'decorations' => $payload['decorations'] ?? null,
            'banner_count' => $payload['bannerCount'] ?? $payload['banner_count'] ?? 2,
            'banners' => $payload['banners'] ?? [],
        ];

        if ($merge) {
            if (isset($payload['animation'])) {
                $data['animation'] = $payload['animation'];
            }
            if (isset($payload['decorations'])) {
                $data['decorations'] = $payload['decorations'];
            }
            if (isset($payload['banners'])) {
                $data['banners'] = $payload['banners'];
            }
            if (isset($payload['bannerCount']) || isset($payload['banner_count'])) {
                $data['banner_count'] = $payload['bannerCount'] ?? $payload['banner_count'];
            }
        }

        $existing = Config::first();
        if (!$existing) {
            $defaults = [
                'animation' => ['enabled' => true, 'type' => 'floating-hearts', 'direction' => 'bottom-to-top', 'scope' => 'all', 'count' => 14],
                'decorations' => ['enabled' => true, 'scope' => 'all'],
                'banner_count' => 2,
                'banners' => [],
            ];
            $data = array_merge($defaults, array_filter($data, fn ($v) => $v !== null));
            $config = Config::create($data);
            return response()->json($config->toApiArray(), $createdStatus);
        }

        if ($merge) {
            $current = $existing->toArray();
            $data = [
                'animation' => $data['animation'] ?? $current['animation'],
                'decorations' => $data['decorations'] ?? $current['decorations'],
                'banner_count' => $data['banner_count'] ?? $current['banner_count'],
                'banners' => $data['banners'] ?? $current['banners'],
            ];
        }

        $existing->update($data);
        return response()->json($existing->fresh()->toApiArray());
    }
}
