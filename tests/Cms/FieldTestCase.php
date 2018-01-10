<?php

namespace Kirby\Cms;

class FieldTestCase extends TestCase
{

    public function setUp()
    {
        $this->kirby();
    }

    public function props(): array
    {
        return [];
    }

    public function type(): string
    {
        return 'text';
    }

    public function field($propsData = [])
    {
        return new Field($this->type(), array_merge($this->props(), $propsData));
    }

    public function testDisabled()
    {
        $this->assertTrue($this->field(['disabled' => true])->disabled());
        $this->assertFalse($this->field(['disabled' => false])->disabled());
    }

    public function testName()
    {
        $this->assertEquals('test', $this->field(['name' => 'test'])->name());
    }

    public function testByDefaultNotRequired()
    {
        $this->assertFalse($this->field()->required());
    }

    public function testRequired()
    {
        $this->assertTrue($this->field(['required' => true])->required());
    }

    public function testHelp()
    {
        $this->assertEquals('test', $this->field(['help' => 'test'])->help());
    }

    public function testHelpI18n()
    {
        $help = [
            'en' => 'english',
            'de' => 'deutsch'
        ];

        $field = $this->field([
            'help' => $help,
            'i18n' => 'en'
        ]);

        $this->assertEquals('english', $field->help());

        $field = $this->field([
            'help' => $help,
            'i18n' => 'de'
        ]);

        $this->assertEquals('deutsch', $field->help());

    }

    public function testLabel()
    {
        $this->assertEquals('test', $this->field(['label' => 'test'])->label());
    }

    public function testLabelI18n()
    {
        $label = [
            'en' => 'english',
            'de' => 'deutsch'
        ];

        $field = $this->field([
            'label' => $label,
            'i18n'  => 'en'
        ]);

        $this->assertEquals('english', $field->label());

        $field = $this->field([
            'label' => $label,
            'i18n'  => 'de'
        ]);

        $this->assertEquals('deutsch', $field->label());

    }

}
