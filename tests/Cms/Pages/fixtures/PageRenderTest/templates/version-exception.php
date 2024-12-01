<?php

use Kirby\Content\VersionId;
use Kirby\Exception\Exception;
use PHPUnit\Framework\AssertionFailedError;

// validate the correct test scenario
if (VersionId::$render?->value() !== 'changes') {
	throw new AssertionFailedError('Version ID is not changes');
}

// this one is expected and used in the test
throw new Exception('Something went wrong');
