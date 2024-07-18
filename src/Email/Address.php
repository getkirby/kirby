<?php

namespace Kirby\Email;

use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\V;

/**
 * An email address
 *
 * @package   Kirby Email
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.0.0
 */
class Address
{
	public function __construct(
		protected string $email,
		public string|null $name = null
	) {
		if (V::email($email) === false) {
			throw new InvalidArgumentException(sprintf('"%s" is not a valid email address', $email));
		}
	}

	/**
	 * Creates one or multiple address objects from a
	 * User object, simple string or email-name key-value pairs
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException email address is invalid
	 */
	public static function factory(
		Users|array|User|string $emails,
		bool $multiple = false
	): static|array {
		// ensure we can iterate over $emails
		if (is_iterable($emails) === false) {
			$emails = A::wrap($emails);
		}

		$addresses = [];

		foreach ($emails as $address => $name) {
			$addresses[] = match (true) {
				$name instanceof User => new static(
					email: $name->email(),
					name: $name->name()
				),

				is_string($address) => new static(
					email: $address,
					name: $name
				),

				default => new static(email: $name)
			};
		}

		if ($multiple === false) {
			return $addresses[0];
		}

		return $addresses;
	}

	/**
	 * Returns the email address
	 */
	public function email(): string
	{
		return $this->email;
	}

	/**
	 * Returns the name, if available
	 */
	public function name(): string|null
	{
		return $this->name;
	}
}
