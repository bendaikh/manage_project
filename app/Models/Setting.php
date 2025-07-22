<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * Get logo URL
     */
    public static function getLogoUrl()
    {
        $logoPath = self::getValue('app_logo');
        if ($logoPath) {
            return asset('storage/' . $logoPath);
        }
        return null;
    }

    /**
     * Get delivery price
     */
    public static function getDeliveryPrice()
    {
        return (float) self::getValue('delivery_price', 0);
    }

    /**
     * Get seller delivery price
     */
    public static function getSellerDeliveryPrice()
    {
        return (float) self::getValue('seller_delivery_price', self::getDeliveryPrice());
    }

    /**
     * Get country name
     */
    public static function getCountry()
    {
        return self::getValue('country', 'Default Country');
    }
}
