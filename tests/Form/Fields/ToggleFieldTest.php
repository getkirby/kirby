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
            'text' => 'Yay'
        ]);

        $this->assertEquals('Yay', $field->text());
    }

    public function testTextWithTranslation()
    {
        $props = [
            'text' => [
                'en' => 'Yay',
                'de' => 'Ja'
            ]
        ];

        I18n::$locale = 'en';

        $field = $this->field('toggle', $props);
        $this->assertEquals('Yay', $field->text());

        I18n::$locale = 'de';

        $field = $this->field('toggle', $props);
        $this->assertEquals('Ja', $field->text());
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
                'Yes',
                'No'
            ]
        ]);

        $this->assertEquals(['Yes', 'No'], $field->text());
    }

    public function testTextToggleWithTranslation()
    {
        $props = [
            'text' => [
                ['en' => 'Yes', 'de' => 'Ja'],
                ['en' => 'No', 'de' => 'Nein']
            ]
        ];

        I18n::$locale = 'en';

        $field = $this->field('toggle', $props);
        $this->assertEquals(['Yes', 'No'], $field->text());

        I18n::$locale = 'de';

        $field = $this->field('toggle', $props);
        $this->assertEquals(['Ja', 'Nein'], $field->text());
    }
}
