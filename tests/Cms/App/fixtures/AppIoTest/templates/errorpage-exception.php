<?php

use Kirby\Exception\ErrorPageException;

throw new ErrorPageException(
	fallback: 'Exception message',
	httpCode: 403
);
