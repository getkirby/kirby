<?php

namespace Kirby\Form;

use TypeError;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionMethod;
use Kirby\Util\I18n;

abstract class FieldTestCase extends BaseTestCase
{

    use Assertions\Autocomplete;
    use Assertions\Converter;
    use Assertions\Disabled;
    use Assertions\Help;
    use Assertions\Icon;
    use Assertions\Label;
    use Assertions\MaxLength;
    use Assertions\MinLength;
    use Assertions\Name;
    use Assertions\Placeholder;
    use Assertions\Required;
    use Assertions\Type;
    use Assertions\Value;
    use Assertions\Width;

    public function assertPropertyCanBeNull(string $property)
    {
        $this->assertNull($this->field([$property => null])->$property());
    }

    public function assertPropertyDefault(string $property, $default)
    {
        $props = $this->defaultProperties();
        unset($props[$property]);

        $field = $this->fieldInstance($props);

        // by not setting the property
        $this->assertEquals($default, $field->$property());

        // by setting the property to null
        $this->assertEquals($default, $this->field([$property => null])->$property());
    }

    public function assertPropertyIsBool(string $property)
    {
        $this->assertTrue($this->field([$property => true])->$property());
        $this->assertFalse($this->field([$property => false])->$property());
    }

    public function assertPropertyIsRequired(string $property)
    {
        $method = new ReflectionMethod($this->className(), 'set' . $property);
        $param  = $method->getParameters()[0];

        $this->assertFalse($param->isOptional());
    }

    public function assertPropertyIsOptional(string $property)
    {
        $method = new ReflectionMethod($this->className(), 'set' . $property);
        $param  = $method->getParameters()[0];

        $this->assertTrue($param->isOptional());
    }

    public function assertPropertyValue(string $property, $value)
    {
        $this->assertEquals($value, $this->field([$property => $value])->$property());
    }

    public function assertPropertyValues(string $property, array $values)
    {
        foreach ($values as $value) {
            $this->assertPropertyValue($property, $value);
        }
    }

    public function assertPropertyTranslate(string $property)
    {
        // simple
        $this->assertPropertyValue($property, 'test');

        I18n::$locale = 'en';

        // translate
        $text = [
            'en' => 'english',
            'de' => 'deutsch'
        ];

        $field = $this->field([
            $property => $text
        ]);

        $this->assertEquals('english', $field->$property());

        I18n::$locale = 'de';

        $field = $this->field([
            $property => $text
        ]);

        $this->assertEquals('deutsch', $field->$property());

    }

    abstract public function className();

    public function defaultProperties(): array
    {
        return [];
    }

    public function field(array $propsData = [])
    {
        return $this->fieldInstance(array_merge($this->defaultProperties(), $propsData));
    }

    public function fieldInstance(array $props = [])
    {
        $className = $this->className();
        return new $className($props);
    }

    public function testDisabled()
    {
        $this->assertDisabledProperty();
    }

    abstract public function testName();
    abstract public function testType();

    public function testWidth()
    {
        $this->assertWidthProperty();
    }

}
