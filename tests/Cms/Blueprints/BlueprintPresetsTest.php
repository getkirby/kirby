<?php

namespace Kirby\Cms;

class BlueprintPresetsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function load($preset)
    {
        return include $this->app->root('kirby') . '/config/presets/' . $preset . '.php';
    }

    /**
     * Page
     */
    public function testPagePresetDefault()
    {
        $preset = $this->load('page');

        // default setup
        $props = $preset([]);

        $expected = [
            'columns' => [
                [
                    'width'  => '2/3',
                    'fields' => []
                ],
                [
                    'width' => '1/3',
                    'sections' => [
                        'pages' => [
                            'headline' => 'Pages',
                            'type'     => 'pages',
                            'status'   => 'all',
                            'layout'   => 'list'
                        ],
                        'files' => [
                            'headline' => 'Files',
                            'type'     => 'files',
                            'layout'   => 'list'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testPagePresetNoFiles()
    {
        $preset = $this->load('page');

        // default setup
        $props = $preset([
            'files' => false
        ]);

        $expected = [
            'columns' => [
                [
                    'width'  => '2/3',
                    'fields' => []
                ],
                [
                    'width' => '1/3',
                    'sections' => [
                        'pages' => [
                            'headline' => 'Pages',
                            'type'     => 'pages',
                            'status'   => 'all',
                            'layout'   => 'list'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testPagePresetNoPages()
    {
        $preset = $this->load('page');

        // default setup
        $props = $preset([
            'pages' => false
        ]);

        $expected = [
            'columns' => [
                [
                    'width'  => '2/3',
                    'fields' => []
                ],
                [
                    'width' => '1/3',
                    'sections' => [
                        'files' => [
                            'headline' => 'Files',
                            'type'     => 'files',
                            'layout'   => 'list'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testPagePresetNoSidebar()
    {
        $preset = $this->load('page');

        // default setup
        $props = $preset([
            'pages' => false,
            'files' => false
        ]);

        $expected = [
            'fields' => [],
        ];

        $this->assertEquals($expected, $props);
    }

    public function testPagePresetCustomSidebar()
    {
        $preset = $this->load('page');

        // default setup
        $props = $preset([
            'sidebar' => [
                'test' => [
                    'headline' => 'Test',
                    'type'     => 'pages'
                ]
            ]
        ]);

        $expected = [
            'columns' => [
                [
                    'width'  => '2/3',
                    'fields' => []
                ],
                [
                    'width' => '1/3',
                    'sections' => [
                        'test' => [
                            'headline' => 'Test',
                            'type'     => 'pages',
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    /**
     * Pages
     */
    public function testPagesPresetDefault()
    {
        $preset = $this->load('pages');

        // default setup
        $props = $preset([]);

        $expected = [
            'sections' => [
                'drafts' => [
                    'headline' => 'Drafts',
                    'type'     => 'pages',
                    'layout'   => 'list',
                    'status'   => 'drafts',
                ],
                'listed' => [
                    'headline' => 'Published',
                    'type'     => 'pages',
                    'layout'   => 'list',
                    'status'   => 'listed',
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testPagesPresetWithUnlisted()
    {
        $preset = $this->load('pages');

        // default setup
        $props = $preset([
            'unlisted' => true
        ]);

        $expected = [
            'sections' => [
                'drafts' => [
                    'headline' => 'Drafts',
                    'type'     => 'pages',
                    'layout'   => 'list',
                    'status'   => 'drafts',
                ],
                'unlisted' => [
                    'headline' => 'Unlisted',
                    'type'     => 'pages',
                    'layout'   => 'list',
                    'status'   => 'unlisted',
                ],
                'listed' => [
                    'headline' => 'Published',
                    'type'     => 'pages',
                    'layout'   => 'list',
                    'status'   => 'listed',
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    /**
     * Files
     */
    public function testFilesPresetDefault()
    {
        $preset = $this->load('files');

        // default setup
        $props = $preset([]);

        $expected = [
            'sections' => [
                'files' => [
                    'headline' => 'Files',
                    'type'     => 'files',
                    'layout'   => 'cards',
                    'info'     => '{{ file.dimensions }}',
                    'template' => null,
                    'image'    => null
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testFilesPresetWithHeadline()
    {
        $preset = $this->load('files');

        // default setup
        $props = $preset([
            'headline' => 'Images'
        ]);

        $expected = [
            'sections' => [
                'files' => [
                    'headline' => 'Images',
                    'type'     => 'files',
                    'layout'   => 'cards',
                    'info'     => '{{ file.dimensions }}',
                    'template' => null,
                    'image'    => null
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testFilesPresetWithLayout()
    {
        $preset = $this->load('files');

        // default setup
        $props = $preset([
            'layout' => 'list'
        ]);

        $expected = [
            'sections' => [
                'files' => [
                    'headline' => 'Files',
                    'type'     => 'files',
                    'layout'   => 'list',
                    'info'     => '{{ file.dimensions }}',
                    'template' => null,
                    'image'    => null
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testFilesPresetWithTemplate()
    {
        $preset = $this->load('files');

        // default setup
        $props = $preset([
            'template' => 'image'
        ]);

        $expected = [
            'sections' => [
                'files' => [
                    'headline' => 'Files',
                    'type'     => 'files',
                    'layout'   => 'cards',
                    'info'     => '{{ file.dimensions }}',
                    'template' => 'image',
                    'image'    => null
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }

    public function testFilesPresetWithImage()
    {
        $preset = $this->load('files');

        // default setup
        $props = $preset([
            'image' => 'icon'
        ]);

        $expected = [
            'sections' => [
                'files' => [
                    'headline' => 'Files',
                    'type'     => 'files',
                    'layout'   => 'cards',
                    'info'     => '{{ file.dimensions }}',
                    'template' => null,
                    'image'    => 'icon'
                ]
            ]
        ];

        $this->assertEquals($expected, $props);
    }
}
