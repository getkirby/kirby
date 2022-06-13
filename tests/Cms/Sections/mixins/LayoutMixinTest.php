<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LayoutMixinTest extends TestCase
{
    protected $app;
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->page = new Page(['slug' => 'test']);

        Section::$types['test'] = Section::$types['pages'] = [
            'mixins' => ['layout'],
            'props'  => $props = [
                'info' => function (string $info = null) {
                    return $info;
                },
                'text' => function (string $text = null) {
                    return $text;
                }
            ]
        ];
    }

    public function testColumns()
    {
        $section = new Section('test', [
            'model' => $this->page,
        ]);

        $expected = [
            'image' => [
                'label' => ' ',
                'type'  => 'image',
                'width' => 'var(--table-row-height)'
            ]
        ];

        $this->assertSame($expected, $section->columns());
    }

    public function testColumnsWithText()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'text'  => '{{ page.title }}'
        ]);

        $expected = [
            'image' => [
                'label' => ' ',
                'type'  => 'image',
                'width' => 'var(--table-row-height)'
            ],
            'title' => [
                'label' => 'Title',
                'type'  => 'url'
            ]
        ];

        $this->assertSame($expected, $section->columns());
    }

    public function testColumnsWithTextAndInfo()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'text'  => '{{ page.title }}',
            'info'  => '{{ page.date }}'
        ]);

        $expected = [
            'image' => [
                'label' => ' ',
                'type'  => 'image',
                'width' => 'var(--table-row-height)',
            ],
            'title' => [
                'label' => 'Title',
                'type'  => 'url'
            ],
            'info' => [
                'label' => 'Info',
                'type'  => 'text'
            ]
        ];

        $this->assertSame($expected, $section->columns());
    }

    public function testColumnsWithFlag()
    {
        $section = new Section('pages', [
            'model' => $this->page
        ]);

        $expected = [
            'image' => [
                'label' => ' ',
                'type'  => 'image',
                'width' => 'var(--table-row-height)',
            ],
            'flag' => [
                'label' => ' ',
                'type'  => 'flag',
                'width' => 'var(--table-row-height)'
            ]
        ];

        $this->assertSame($expected, $section->columns());
    }

    public function testColumnsWithCustomColumns()
    {
        $section = new Section('test', [
            'model' => $this->page,
            'columns' => [
                'date' => [
                    'label' => 'Date',
                    'type'  => 'date'
                ]
            ]
        ]);

        $expected = [
            'image' => [
                'label' => ' ',
                'type'  => 'image',
                'width' => 'var(--table-row-height)',
            ],
            'dateCell' => [
                'label' => 'Date',
                'type'  => 'date',
                'id'    => 'date'
            ]
        ];

        $this->assertSame($expected, $section->columns());
    }

    public function testColumnsValues()
    {
        $model = new Page([
            'slug' => 'test',
            'content' => [
                'title' => 'Test Page',
                'date'  => '2012-12-12',
                'html'  => '<i>Some HTML</i>'
            ]
        ]);

        $section = new Section('test', [
            'model' => $model,
            'text'  => '{{ page.title }}',
            'info'  => '{{ page.slug }}',
            'columns' => [
                'date' => [
                    'label' => 'Date',
                    'type'  => 'date'
                ],
                'html' => [
                    'label' => 'HTML',
                    'type'  => 'html',
                    'value' => '{{ page.html }}'
                ],
                'safeHtml' => [
                    'label' => 'Safe HTML',
                    'value' => '{{ page.html }}'
                ]
            ]
        ]);

        $item = [
            'text' => 'Test Page',
            'info' => 'test'
        ];

        $expected = [
            'text' => 'Test Page',
            'info' => 'test',
            'title' => [
                'text' => 'Test Page',
                'href' => '/pages/test'
            ],
            'image' => null,
            'dateCell' => '2012-12-12',
            'htmlCell' => '<i>Some HTML</i>',
            'safeHtmlCell' => '&lt;i&gt;Some HTML&lt;/i&gt;'
        ];

        $this->assertSame($expected, $section->columnsValues($item, $model));
    }


    public function testLayout()
    {
        // default
        $section = new Section('test', [
            'model' => $this->page,
        ]);

        $this->assertSame('list', $section->layout());

        // custom
        $section = new Section('test', [
            'model'  => $this->page,
            'layout' => 'cardlets'
        ]);

        $this->assertSame('cardlets', $section->layout());

        // invalid with fallback
        $section = new Section('test', [
            'model'  => $this->page,
            'layout' => 'foo'
        ]);

        $this->assertSame('list', $section->layout());
    }

    public function testSize()
    {
        // default
        $section = new Section('test', [
            'model' => $this->page,
        ]);

        $this->assertSame('auto', $section->size());

        // custom
        $section = new Section('test', [
            'model' => $this->page,
            'size'  => 'large'
        ]);

        $this->assertSame('large', $section->size());
    }
}
