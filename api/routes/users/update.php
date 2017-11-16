<?php

use Kirby\Cms\Input;

return [
    'auth'    => true,
    'pattern' => 'users/(.*?)',
    'method'  => 'POST',
    'action'  => function ($id) {

        $user  = $this->users()->find($id);
        $input = $this->input();
        $data  = (new Input($user, $input))->toArray();

        return $this->output('user', $user->update($data));

    }
];
