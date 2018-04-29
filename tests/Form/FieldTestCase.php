<?php

namespace Kirby\Form;

use ReflectionMethod;
use Kirby\Util\I18n;

abstract class FieldTestCase extends \PHPUnit\Framework\TestCase
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

    static protected $type;

    public function setUp()
    {
        Field::$types[static::$type] = dirname(dirname(__DIR__)) . '/config/fields/' . ucfirst(static::$type) . 'Field.php';
    }

    public function tearDown()
    {
        Field::$types = [];
    }

    public function assertPropertyCanBeNull(string $property)
    {
        $this->assertNull($this->field([$property => null])->$property());
    }

    public function assertPropertyDefault(string $property, $default)
    {
        // by not setting the property
        $this->assertEquals($default, $this->field()->$property());

        // by setting the property to null
        $this->assertEquals($default, $this->field([$property => null])->$property());
    }

    public function assertPropertyIsBool(string $property)
    {
        $this->assertTrue($this->field([$property => true])->$property());
        $this->assertFalse($this->field([$property => false])->$property());
    }

    public function assertPropertyValue(string $property, $value, $expected = null)
    {
        $this->assertEquals($expected ?? $value, $this->field([$property => $value])->$property());
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

    public function field(array $propsData = [])
    {
        return Field::factory(array_merge(
            $propsData,
            ['type' => static::$type]
        ));
    }

    public function testDisabled()
    {
        $this->assertDisabledProperty();
    }

    public function testHelp()
    {
        $this->assertHelpProperty();
    }

    /**
     * @group failing
     */
    public function testLabel()
    {
        return $this->assertLabelProperty(ucfirst(static::$type));
    }

    public function testName()
    {
        $this->assertNameProperty(static::$type);
    }

    public function testType()
    {
        $this->assertTypeProperty(static::$type);
    }

    public function testWidth()
    {
        $this->assertWidthProperty();
    }

}
