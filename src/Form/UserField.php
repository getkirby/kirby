<?php

namespace Kirby\Form;

use Kirby\Cms\App;

use Kirby\Exception\NotFoundException;

class UserField extends Field
{

    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\Required;
    use Mixins\Value;

    protected $options;

    protected function defaultDefault()
    {
        if ($user = App::instance()->user()) {
            return $user->id();
        }
    }

    protected function defaultIcon()
    {
        return 'user';
    }

    protected function defaultLabel()
    {
        return 'User';
    }

    protected function defaultName(): string
    {
        return 'user';
    }

    public function options(): array
    {

        $options = [];

        foreach (App::instance()->users() as $user) {
            $options[] = [
                'value' => $user->id(),
                'text' => $user->name(),
                'image' => $user->avatar()->exists() ? $user->avatar()->url() : null
            ];
        }

        return $options;
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);
        $this->validateUser($value);

        return true;
    }

    protected function validateUser($value)
    {
        if ($value !== null) {
            if (App::instance()->user($value) === null) {
                throw new NotFoundException([
                    'key'  => 'user.notFound',
                    'data' => ['name' => $value]
                ]);
            }
        }

        return true;
    }

}
