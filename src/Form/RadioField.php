<?php

namespace Kirby\Form;

class RadioField extends Field
{

    use Mixins\Autofocus;
    use Mixins\Columns;
    use Mixins\Label;
    use Mixins\Options;
    use Mixins\Required;
    use Mixins\Value;

    protected function validate($value)
    {
        $this->validateRequired($value);
        $this->validateSingleOption($value);

        return true;
    }

}
