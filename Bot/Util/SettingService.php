<?php

namespace Bot\Util;

class SettingService
{
    private static $defaultSettings = [
        'linked_name' => false
    ];

    public static function handle($data)
    {
        $data = json_decode($data);

        foreach (array_keys(static::$defaultSettings) as $defaultSetting) {
            if (! property_exists($data, $defaultSetting)) {
                $data->$defaultSetting = static::$defaultSettings[$defaultSetting];
            }
        }

        return $data;
    }
}