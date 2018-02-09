<?php

namespace Kirby\Form;

class TextField extends Field
{

    use Mixins\Autocomplete;
    use Mixins\Autofocus;
    use Mixins\Converter;
    use Mixins\Counter;
    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\Length;
    use Mixins\Placeholder;
    use Mixins\Required;
    use Mixins\Value;

    protected function defaultLabel()
    {
        return 'Text';
    }

    protected function defaultName(): string
    {
        return 'text';
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);
        $this->validateLength($value);

        return true;
    }

    protected function valueFromInput($input)
    {
        return $this->convert($input);
    }

}
