<?php

namespace Kirby\Panel\Controller\Request {
	/**
	 * Mock for the PHP password_hash() function to reduce the cost
	 * while testing
	 */
	function password_hash(
		string $password,
		string|int|null $algo,
		array $options = []
	): string|false {
		\Kirby\Tests\ensureTesting('password_hash');
		$options['cost'] ??= 4;
		return \password_hash($password, $algo, $options);
	}
}
