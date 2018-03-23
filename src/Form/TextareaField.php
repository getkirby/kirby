<?php

namespace Kirby\Form;

class TextareaField extends Field
{

    use Mixins\Counter;
    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\Length;
    use Mixins\Multiline;
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

}
