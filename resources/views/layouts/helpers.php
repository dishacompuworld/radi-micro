<?php

use App\Models\Setting;

if (!function_exists('appSettings')) {
    function appSettings($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}