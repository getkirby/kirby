<?php

namespace Kirby\Form;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use Kirby\Form\Exceptions\PropertyException;
use Kirby\Util\I18n;

class Field extends Component
{

    use I18n;
    use Mixins\Model;

    protected $disabled;
    protected $name;
    protected $type;
    protected $width;

    protected function defaultDisabled(): bool
    {
        return false;
    }

    protected function defaultName()
    {
        return null;
    }

    public function disabled(): bool
    {
        return $this->disabled;
    }

    protected function defaultWidth(): string
    {
        return '1/1';
    }

    public static function factory(array $props)
    {
        if (isset($props['type']) === false) {
            throw new PropertyException('Missing field type');
        }

        $fieldClass = __NAMESPACE__ . '\\' . ucfirst($props['type']) . 'Field';

        if (class_exists($fieldClass) === false) {
            throw new PropertyException(sprintf('Invalid field type: "%s"', $props['type']));
        }

        return new $fieldClass($props);
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param boolean $disabled
     * @return self
     */
    protected function setDisabled(bool $disabled = false): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Set the field name
     *
     * @param string $name
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    protected function setWidth(string $width = '1/1'): self
    {
        $this->width = $width;
        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // never include the model in the output
        unset($array['model']);

        return $array;
    }

    public function type(): string
    {
        $className = get_called_class();
        $className = substr($className, strrpos($className, '\\') + 1);
        $className = str_replace('Field', '', $className);
        $className = strtolower($className);

        return $className;
    }

    public function width(): string
    {
        return $this->width;
    }

}
