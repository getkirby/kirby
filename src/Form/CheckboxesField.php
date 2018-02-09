<?php

namespace Kirby\Form;

use Kirby\Util\Str;

class CheckboxesField extends Field
{

    use Mixins\Autofocus;
    use Mixins\Columns;
    use Mixins\Label;
    use Mixins\Options;
    use Mixins\Required;
    use Mixins\Value;

    protected function valueFromInput($input)
    {
        return $this->valueFromList($input, ', ');
    }

    protected function valueToString($value): string
    {
        return $this->valueToList($value, ', ');
    }

    protected function validate($value)
    {
        $this->validateRequired($value);
        $this->validateMultipleOptions($value);

        return true;
    }

}
