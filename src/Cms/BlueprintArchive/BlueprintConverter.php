<?php

namespace Kirby\Cms;

/**
 * Converts simplified blueprint setups into a
 * normalized blueprint with tabs and sections.
 */
class BlueprintConverter
{
    public static function convertColumnsToTabs(array $data): array
    {
        if (isset($data['columns']) === false || isset($data['tabs']) === true) {
            return $data;
        }

        $data['tabs'] = [
            'main' => [
                'label'   => 'Main',
                'columns' => $data['columns']
            ]
        ];

        unset($data['columns']);

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

    public static function convertSectionsToColumns(array $data): array
    {
        if (isset($data['sections']) === false || isset($data['columns']) === true) {
            return $data;
        }

        $data['columns'] = [
            ['1/1' => implode(',', array_keys((array)$data['sections']))]
        ];

        return $data;
    }
}
