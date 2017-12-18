<?php

namespace Kirby\Cms;

class BlueprintConverter
{

    public static function convert(array $data): array
    {

        if (isset($data['tabs']) === true) {
            return $data;
        }

        if (isset($data['columns']) === true) {
            return static::convertColumnsToTab($data);
        }

        if (isset($data['fields']) === true) {
            $data = static::convertFieldsToColumn($data);
            $data = static::convertColumnsToTab($data);
            return $data;
        }

    }

    public static function convertColumnsToTab(array $data): array
    {
        $data['tabs'] = [
            [
                'name'    => 'main',
                'columns' => $data['columns']
            ]
        ];

        unset($data['columns']);

        return $data;
    }

    public static function convertFieldsToColumn(array $data): array
    {
        $data['columns'] = [
            [
                'type'   => 'fields',
                'fields' => $data['fields']
            ]
        ];

        unset($data['fields']);

        return $data;
    }

}
