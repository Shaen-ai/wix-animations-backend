<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'banner_id' => 'hanging-hearts',
                'label' => 'Hanging Hearts',
                'image' => 'https://cdn.shopify.com/s/files/1/0395/1797/8787/t/6/assets/hanging_red_hearts.png',
                'default_position' => 'top',
                'default_dismiss_on_click' => true,
            ],
            [
                'banner_id' => 'fourth-logo',
                'label' => 'Fourth Logo',
                'image' => 'https://cdn.shopify.com/s/files/1/0395/1797/8787/t/6/assets/fourth_logo.png',
                'default_position' => 'top',
                'default_dismiss_on_click' => true,
            ],
            [
                'banner_id' => 'santa-bag',
                'label' => 'Santa Bag',
                'image' => 'https://cdn.shopify.com/s/files/1/0395/1797/8787/t/6/assets/santa-claus-bag.png',
                'default_position' => 'bottom',
                'default_dismiss_on_click' => true,
            ],
            [
                'banner_id' => 'bottom-hearts',
                'label' => 'Bottom Hearts',
                'image' => 'https://cdn.shopify.com/s/files/1/0395/1797/8787/t/6/assets/bottom_left_hearts.png',
                'default_position' => 'bottom',
                'default_dismiss_on_click' => true,
            ],
            [
                'banner_id' => 'sparkle-stars',
                'label' => 'Sparkle Stars',
                'image' => '',
                'default_position' => 'top',
                'default_dismiss_on_click' => true,
            ],
            [
                'banner_id' => 'confetti-celebration',
                'label' => 'Confetti Celebration',
                'image' => '',
                'default_position' => 'bottom',
                'default_dismiss_on_click' => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(
                ['banner_id' => $banner['banner_id']],
                $banner
            );
        }
    }
}
