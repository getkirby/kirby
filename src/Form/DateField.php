<?php

namespace Kirby\Form;

class DateField extends Field
{

    use Mixins\Date;
    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\Required;
    use Mixins\Value;

    protected $range;

    protected function defaultIcon()
    {
        return 'calendar';
    }

    protected function defaultLabel()
    {
        return 'Date';
    }

    protected function defaultName(): string
    {
        return 'date';
    }

    protected function defaultRange(): int
    {
        return 10;
    }

    public function range()
    {
        return $this->range;
    }

    protected function setRange(int $range = null)
    {
        $this->range = $range;
        return $this;
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);
        $this->validateDate($value);

        return true;
    }

    protected function valueFromInput($input)
    {
        return $this->dateFromInput($input);
    }

    protected function valueToString($value)
    {
        return $this->dateToString($value);
    }

}
