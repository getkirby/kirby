<?php

namespace Kirby\Form\Fields;

use Kirby\Toolkit\I18n;

class ToggleFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('toggle');

        $this->assertEquals('toggle', $field->type());
        $this->assertEquals('toggle', $field->name());
        $this->assertEquals(null, $field->value());
        $this->assertTrue($field->save());
    }

    public function testText()
    {
        $field = $this->field('toggle', [
            'text' => 'Yay {{ page.slug }}'
        ]);

        $this->assertEquals('Yay test', $field->text());
    }

    public function testTextWithTranslation()
    {
        $props = [
            'text' => [
                'en' => 'Yay {{ page.slug }}',
                'de' => 'Ja {{ page.slug }}'
            ]
        ];

        I18n::$locale = 'en';

        $field = $this->field('toggle', $props);
        $this->assertEquals('Yay test', $field->text());

        I18n::$locale = 'de';

        $field = $this->field('toggle', $props);
        $this->assertEquals('Ja test', $field->text());
    }

    public function testBooleanDefaultValue()
    {
        // true
        $field = $this->field('toggle', [
            'default' => true
        ]);

        $this->assertTrue($field->default() === true);

        // false
        $field = $this->field('toggle', [
            'default' => false
        ]);

        $this->assertTrue($field->default() === false);
    }

    public function testTextToggle()
    {
        $field = $this->field('toggle', [
            'text' => [
                'Yes {{ page.slug }}',
                'No {{ page.slug }}'
            ]
        ]);

        $this->assertEquals(['Yes test', 'No test'], $field->text());
    }

    public function testTextToggleWithTranslation()
    {
        $props = [
            'text' => [
                ['en' => 'Yes {{ page.slug }}', 'de' => 'Ja {{ page.slug }}'],
                ['en' => 'No {{ page.slug }}', 'de' => 'Nein {{ page.slug }}']
            ]
        ];

        I18n::$locale = 'en';

        $field = $this->field('toggle', $props);
        $this->assertEquals(['Yes test', 'No test'], $field->text());

        I18n::$locale = 'de';

        $field = $this->field('toggle', $props);
        $this->assertEquals(['Ja test', 'Nein test'], $field->text());
    }
}
