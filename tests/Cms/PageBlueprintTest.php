<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PageBlueprintTest extends TestCase
{

    public function testOptions()
    {
        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test'])
        ]);

        $expected = [
            'changeSlug'     => null,
            'changeStatus'   => null,
            'changeTemplate' => null,
            'changeTitle'    => null,
            'create'         => null,
            'delete'         => null,
            'preview'        => null,
            'read'           => null,
            'sort'           => null,
            'update'         => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }

    public function numProvider()
    {
        return [
            ['default', 'default'],
            ['sort', 'default'],
            ['zero', 'zero'],
            [0, 'zero'],
            ['0', 'zero'],
            ['date', 'date'],
            ['datetime', 'datetime'],
            ['{{ page.something }}', '{{ page.something }}'],
        ];
    }

    /**
     * @dataProvider numProvider
     */
    public function testNum($input, $expected)
    {
        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test']),
            'num'   => $input
        ]);

        $this->assertEquals($expected, $blueprint->num());

    }

    public function testStatus()
    {
        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => [
                'draft'    => 'Draft Label',
                'unlisted' => 'Unlisted Label',
                'listed'   => 'Listed Label'
            ]
        ]);

        $expected = [
            'draft' => [
                'label' => 'Draft Label',
                'text'  => null
            ],
            'unlisted' => [
                'label' => 'Unlisted Label',
                'text'  => null
            ],
            'listed' => [
                'label' => 'Listed Label',
                'text'  => null
            ]
        ];

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testStatusWithCustomText()
    {
        $expected = [
            'draft' => [
                'label' => 'Draft Label',
                'text'  => 'Draft Text'
            ],
            'unlisted' => [
                'label' => 'Unlisted Label',
                'text'  => 'Unlisted Text'
            ],
            'listed' => [
                'label' => 'Listed Label',
                'text'  => 'Listed Text'
            ]
        ];

        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => $expected,
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testStatusTranslations()
    {
        $input = [
            'draft' => [
                'label' => ['en' => 'Draft Label'],
                'text'  => ['en' => 'Draft Text']
            ],
            'unlisted' => [
                'label' => ['en' => 'Unlisted Label'],
                'text'  => ['en' => 'Unlisted Text']
            ],
            'listed' => [
                'label' => ['en' => 'Listed Label'],
                'text'  => ['en' => 'Listed Text']
            ]
        ];

        $expected = [
            'draft' => [
                'label' => 'Draft Label',
                'text'  => 'Draft Text'
            ],
            'unlisted' => [
                'label' => 'Unlisted Label',
                'text'  => 'Unlisted Text'
            ],
            'listed' => [
                'label' => 'Listed Label',
                'text'  => 'Listed Text'
            ]
        ];

        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => $input,
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }


}
