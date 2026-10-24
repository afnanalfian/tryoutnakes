<?php

namespace App\Services;

class MotivationService
{
    /** Tingkatan persentase (batas bawah => key setting). */
    public const TIERS = [
        100 => ['key' => 'motivation_100', 'label' => 'Perfect', 'emoji' => '🏆', 'tone' => 'gold'],
        90 => ['key' => 'motivation_90', 'label' => 'Luar Biasa', 'emoji' => '🔥', 'tone' => 'emerald'],
        80 => ['key' => 'motivation_80', 'label' => 'Sangat Baik', 'emoji' => '🚀', 'tone' => 'teal'],
        70 => ['key' => 'motivation_70', 'label' => 'Baik', 'emoji' => '💪', 'tone' => 'sky'],
        50 => ['key' => 'motivation_50', 'label' => 'Cukup', 'emoji' => '🌱', 'tone' => 'amber'],
        0 => ['key' => 'motivation_0', 'label' => 'Terus Berlatih', 'emoji' => '🤍', 'tone' => 'rose'],
    ];

    public static function for(float $percentage): array
    {
        foreach (self::TIERS as $min => $tier) {
            if ($min === 100 ? $percentage >= 100 : $percentage >= $min) {
                return $tier + ['min' => $min, 'message' => setting($tier['key'])];
            }
        }

        return self::TIERS[0] + ['min' => 0, 'message' => setting('motivation_0')];
    }

    public static function ranges(): array
    {
        return [
            'motivation_100' => '100% (Perfect)',
            'motivation_90' => '90% – 99%',
            'motivation_80' => '80% – 89%',
            'motivation_70' => '70% – 79%',
            'motivation_50' => '50% – 69%',
            'motivation_0' => '0% – 49%',
        ];
    }
}
