<?php

namespace Kirby\Cms;

class PageBlueprintConverter
{

    protected $data;

    public function __construct(array $blueprint)
    {

        $this->data = [
            'title'  => $blueprint['title'] ?? $blueprint['name'],
            'name'   => $blueprint['name'],
            'layout' => [
                [
                    'width'    => '1/3',
                    'sections' => $this->sidebar($blueprint),
                ],
                [
                    'width'    => '2/3',
                    'sections' => [
                        [
                            'type'   => 'fields',
                                'fields' => $this->fields($blueprint['fields'] ?? [])
                        ]
                    ]
                ]
            ]
        ];

    }

    protected function sidebar(array $blueprint): array
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

    protected function fields(array $fields): array
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

    public function toArray()
    {
        return $this->data;
    }

}
