<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Support\Facades\File;

class BannerService
{
    private const ASSETS_DIR = __DIR__ . '/../../assets/animations';

    private const LEGACY_IDS = [
        'hanging_red_hearts.png' => 'hanging-hearts',
        'fourth_logo.png' => 'fourth-logo',
        'santa-claus-bag.png' => 'santa-bag',
        'bottom_left_hearts.png' => 'bottom-hearts',
        'sparkle_stars.svg' => 'sparkle-stars',
        'confetti_celebration.svg' => 'confetti-celebration',
    ];

    private const EXTENSIONS = ['.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp'];

    public function getBanners(string $baseUrl): array
    {
        $fromFiles = $this->getBannersFromFiles($baseUrl);
        if (count($fromFiles) > 0) {
            return $fromFiles;
        }
        return Banner::all()->map(fn (Banner $b) => $b->toApiArray($baseUrl))->toArray();
    }

    public function getBannersFromFiles(string $baseUrl): array
    {
        $assetsPath = base_path('../assets/animations');
        if (!File::isDirectory($assetsPath)) {
            return [];
        }

        $files = File::files($assetsPath);
        $byId = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (!$this->isValidExtension($filename)) {
                continue;
            }

            $ext = $file->getExtension();
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $id = self::LEGACY_IDS[$filename] ?? strtolower(str_replace('_', '-', $base));
            $isPng = strtolower($ext) === 'png';
            $existing = $byId[$id] ?? null;

            if (!$existing || ($isPng && ($existing['ext'] ?? '') !== 'png')) {
                $label = ucwords(str_replace(['-', '_'], ' ', $base));
                $byId[$id] = [
                    'id' => $id,
                    'label' => $label,
                    'filename' => $filename,
                    'ext' => strtolower($ext),
                ];
            }
        }

        $result = array_map(function ($item) use ($baseUrl) {
            return [
                'id' => $item['id'],
                'label' => $item['label'],
                'image' => rtrim($baseUrl, '/') . '/assets/animations/' . rawurlencode($item['filename']),
                'defaultPosition' => 'top',
                'defaultDismissOnClick' => true,
            ];
        }, array_values($byId));

        $result[] = [
            'id' => 'happy-new-year',
            'label' => 'Happy New Year',
            'image' => 'https://storage.apiboomtech.com/file?f=696665071203e4a43ca27821',
            'defaultPosition' => 'bottom',
            'defaultDismissOnClick' => true,
        ];

        return $result;
    }

    private function isValidExtension(string $filename): bool
    {
        $lower = strtolower($filename);
        foreach (self::EXTENSIONS as $ext) {
            if (str_ends_with($lower, $ext)) {
                return true;
            }
        }
        return false;
    }
}
