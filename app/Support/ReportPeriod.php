<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class ReportPeriod
{
    public const DEFAULT = '30days';

    public const KEYS = ['today', '7days', '30days'];

    /**
     * @return array{key: string, label: string, from: Carbon, from_label: string}
     */
    public static function resolve(?string $key): array
    {
        $key = in_array($key, self::KEYS, true) ? $key : self::DEFAULT;

        $def = self::definitions()[$key];

        return [
            'key'        => $key,
            'label'      => $def['label'],
            'from'       => $def['from'],
            'from_label' => $def['from']->format('d/m/Y'),
        ];
    }

    /**
     * @return array<string, array{label: string, from: Carbon}>
     */
    public static function definitions(): array
    {
        return [
            'today' => [
                'label' => 'اليوم',
                'from'  => now()->startOfDay(),
            ],
            '7days' => [
                'label' => 'آخر 7 أيام',
                'from'  => now()->subDays(6)->startOfDay(),
            ],
            '30days' => [
                'label' => 'آخر 30 يوم',
                'from'  => now()->subDays(29)->startOfDay(),
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public static function options(): array
    {
        return [
            ['key' => 'today', 'label' => 'اليوم'],
            ['key' => '7days', 'label' => '7 أيام'],
            ['key' => '30days', 'label' => '30 يوم'],
        ];
    }
}
