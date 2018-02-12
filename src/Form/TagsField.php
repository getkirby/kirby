<?php

namespace Kirby\Form;

use Kirby\Util\Str;

class TagsField extends Field
{

    use Mixins\Autofocus;
    use Mixins\Converter;
    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\Options;
    use Mixins\Required;
    use Mixins\Separator;
    use Mixins\Value;

    protected function defaultLabel()
    {
        return 'Tags';
    }

    protected function defaultName(): string
    {
        return 'tags';
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);

        return true;
    }

    protected function valueFromInput($input)
    {
        $value = $this->valueFromList($input, $this->separator());
        $value = $this->convert($value);

        return $value;
    }

    protected function valueToString($value)
    {
        return $this->valueToList($value, $this->separator() . ' ');
    }

}
