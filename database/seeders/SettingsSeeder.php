<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_logo',
                'value' => null,
                'type' => 'file',
                'group' => 'appearance',
                'description' => 'Application logo displayed in sidebar and headers',
            ],
            [
                'key' => 'country',
                'value' => 'France',
                'type' => 'string',
                'group' => 'delivery',
                'description' => 'Country name for delivery and billing',
            ],
            [
                'key' => 'delivery_price',
                'value' => '25.00',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Standard delivery price in local currency',
            ],
            [
                'key' => 'app_name',
                'value' => 'Laravel App',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name',
            ],
            [
                'key' => 'app_description',
                'value' => 'Your application description',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application description',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
