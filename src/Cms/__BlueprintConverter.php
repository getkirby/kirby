<?php

namespace Kirby\Cms;

class BlueprintConverter
{

    public static function convert(array $blueprint): array
    {

        $output = [
            'title'  => $blueprint['title'],
            'name'   => $blueprint['name'],
            'layout' => [
                [
                    'width'    => '1/3',
                    'sections' => static::sidebar($blueprint),
                ],
                [
                    'width'    => '2/3',
                    'sections' => [
                        [
                            'type'   => 'fields',
                            'fields' => static::fields($blueprint['fields'])
                        ]
                    ]
                ]
            ]
        ];

        return $output;

    }

    protected static function sidebar(array $blueprint): array
    {

        $sidebar = [];

        if (($blueprint['pages'] ?? true) !== false) {
            $sidebar[] = [
                'headline' => 'Pages',
                'type'     => 'pages',
            ];
        }

        if (($blueprint['files'] ?? true) !== false) {
            $sidebar[] = [
                'headline' => 'Files',
                'type'     => 'files',
            ];
        }

        return $sidebar;

    }

    protected static function fields(array $fields): array
    {

        // add the name to each field
        foreach ($fields as $name => $field) {
            $fields[$name]['name'] = $name;
        }

        // remove the old title field
        unset($fields['title']);

        // remove the array keys
        return array_values($fields);

    }

}
