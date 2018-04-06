<?php

return function () {
    if ($user = $this->user()) {
        return $user;
    }

    throw new Exception('Unauthenticated', 403);
};
