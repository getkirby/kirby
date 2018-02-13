<?php

namespace Kirby\Form;

class SelectField extends Field
{

    use Mixins\Autofocus;
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
