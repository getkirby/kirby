<?php

namespace Kirby\Form;

class DateField extends Field
{

    use Mixins\Autofocus;
    use Mixins\Date;
    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\Required;
    use Mixins\Value;

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
