<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class FieldsSectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/FieldsSectionTest'
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function modelProvider()
    {
        return [
            [
                new Page(['slug' => 'test']),
                true
            ],
            [
                new Site(),
                true
            ],
            [
                new File(['filename' => 'test.jpg']),
                false
            ],
            [
                new User(['email' => 'mail@getkirby.com']),
                false
            ],
        ];
    }

    /**
     * @dataProvider modelProvider
     */
    public function testSkipTitle($model, $skip)
    {
        $fields = [
            'text' => [
                'type' => 'textarea'
            ]
        ];

        if ($skip === false) {
            // add a custom title field to those models
            // which don't skip the title. Files and Users
            // should still be able to have title fields if needed
            $fields['title'] = [
                'type' => 'text'
            ];
        }

        $section = new Section('fields', [
            'name'   => 'test',
            'model'  => $model,
            'fields' => $fields,
        ]);

        if ($skip === true) {
            $this->assertCount(1, $section->fields());
            $this->assertArrayHasKey('text', $section->fields());
            $this->assertArrayNotHasKey('title', $section->fields());
        } else {
            $this->assertCount(2, $section->fields());
            $this->assertArrayHasKey('text', $section->fields());
            $this->assertArrayHasKey('title', $section->fields());
        }
    }
}
