<?php

// ensure we got passed a testing closure
if (isset($closure) !== true || $closure instanceof Closure !== true) {
	throw new Exception('No testing closure available.');
}

// pass the current variable scope to it
$closure(get_defined_vars());
