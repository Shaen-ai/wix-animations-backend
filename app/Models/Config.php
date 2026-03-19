<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'configs';

    protected $fillable = [
        'animation',
        'decorations',
        'banner_count',
        'banners',
    ];

    protected $casts = [
        'animation' => 'array',
        'decorations' => 'array',
        'banners' => 'array',
    ];

    public function toApiArray(): array
    {
        $data = [
            'animation' => $this->animation ?? [
                'enabled' => true,
                'type' => 'flying-cupid',
                'direction' => 'bottom-to-top',
                'scope' => 'all',
                'count' => 3,
            ],
            'decorations' => $this->decorations ?? [
                'enabled' => true,
                'scope' => 'all',
            ],
            'bannerCount' => $this->banner_count ?? 2,
            'banners' => $this->banners ?? [],
        ];

        return array_filter($data, fn ($v) => $v !== null);
    }
}
