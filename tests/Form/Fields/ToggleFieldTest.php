<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;
use Kirby\Toolkit\I18n;

class ToggleFieldTest extends TestCase
{

    public function testDefaultProps()
    {
        $field = new Field([
            'type' => 'toggle',
            'name' => 'toggle'
        ]);

        $this->assertEquals('toggle', $field->type());
        $this->assertEquals('toggle', $field->name());
        $this->assertEquals(null, $field->value());
    }

    public function testText()
    {
        $field = new Field([
            'type' => 'toggle',
            'name' => 'toggle',
            'text' => 'Yay'
        ]);

        $this->assertEquals('Yay', $field->text());
    }

    public function testTextWithTranslation()
    {
        $props = [
            'type' => 'toggle',
            'name' => 'toggle',
            'text' => [
                'en' => 'Yay',
                'de' => 'Ja'
            ]
        ];

        I18n::$locale = 'en';

        $field = new Field($props);
        $this->assertEquals('Yay', $field->text());

        I18n::$locale = 'de';

        $field = new Field($props);
        $this->assertEquals('Ja', $field->text());
    }

    public function testTextToggle()
    {
        $field = new Field([
            'type' => 'toggle',
            'name' => 'toggle',
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
            'type' => 'toggle',
            'name' => 'toggle',
            'text' => [
                ['en' => 'Yes', 'de' => 'Ja'],
                ['en' => 'No', 'de' => 'Nein']
            ]
        ];

        I18n::$locale = 'en';

        $field = new Field($props);
        $this->assertEquals(['Yes', 'No'], $field->text());

        I18n::$locale = 'de';

        $field = new Field($props);
        $this->assertEquals(['Ja', 'Nein'], $field->text());
    }

}
