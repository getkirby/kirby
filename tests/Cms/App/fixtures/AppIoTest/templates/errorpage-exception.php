<?php

throw new Kirby\Exception\ErrorPageException([
    'fallback' => 'Exception message',
    'httpCode' => 403
]);
