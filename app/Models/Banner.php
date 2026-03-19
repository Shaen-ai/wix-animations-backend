<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'banner_id',
        'label',
        'image',
        'default_position',
        'default_dismiss_on_click',
    ];

    protected $casts = [
        'default_dismiss_on_click' => 'boolean',
    ];

    public function toApiArray(string $baseUrl): array
    {
        return [
            'id' => $this->banner_id,
            'label' => $this->label,
            'image' => $this->image,
            'defaultPosition' => $this->default_position,
            'defaultDismissOnClick' => $this->default_dismiss_on_click,
        ];
    }
}
