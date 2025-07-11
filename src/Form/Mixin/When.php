<?php

namespace Kirby\Form\Mixin;

use Kirby\Exception\NotFoundException;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait When
{
	protected array|null $when = null;

	/**
	 * Checks if the field is currently active
	 * or hidden because of a `when` condition
	 */
	public function isActive(array $input = []): bool
	{
		if ($this->when === null || $this->when === []) {
			return true;
		}

		$siblings = $this->siblings();

		foreach ($this->when as $name => $expected) {
			$field = $siblings->get($name);

			if ($field === null) {
				throw new NotFoundException('When condition for field "' . $this->name() . '": no sibling field named "' . $name . '" found');
			}

			$input = $input[$name] ?? $field?->value() ?? '';

			// if the input value doesn't match the requested `when` value,
			// that means that this field is not required and can be saved
			// (*all* `when` conditions must be met for this field to be required)
			if ($input !== $expected) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Setter for the `when` condition
	 */
	protected function setWhen(array|null $when = null): void
	{
		$this->when = $when;
	}

	/**
	 * Returns the `when` condition of the field
	 */
	public function when(): array|null
	{
		return $this->when;
	}
}
