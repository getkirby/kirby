<?php

namespace Kirby\Cms;

class BlueprintConverter
{

    public static function convertColumnsToTab(array $data): array
    {
        if (isset($data['columns']) === false) {
            return $data;
        }

        $data['tabs'] = [
            'main' => [
                'columns' => $data['columns']
            ]
        ];

        unset($data['columns']);

        return $data;
    }

    public static function convertSectionsToColumn(array $data): array
    {
        if (isset($data['sections']) === false) {
            return $data;
        }

        $data['columns'] = [
            'center' => [
                'sections' => $data['sections']
            ]
        ];

        unset($data['sections']);

        return $data;
    }


    public static function convertFieldsToSection(array $data): array
    {
        if (isset($data['fields']) === false) {
            return $data;
        }

        $data['sections'] = [
            'fields' => [
                'type'   => 'fields',
                'fields' => $data['fields']
            ]
        ];

        unset($data['fields']);

        return $data;
    }

}
