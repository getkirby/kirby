<?php

namespace Kirby\Blueprint;

/**
 * Extension
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage in 3.10
 * @codeCoverageIgnore
 */
class Extension
{
	public function __construct(
		public string $path
	) {
	}

	public static function apply(array $props): array
	{
		if (isset($props['extends']) === false) {
			return $props;
		}

		// already extended
		if (is_a($props['extends'], Extension::class) === true) {
			return $props;
		}

		$extension = new static($props['extends']);
		return $extension->extend($props);
	}

	public function extend(array $props): array
	{
		$props = array_replace_recursive(
			$this->read(),
			$props
		);

		$props['extends'] = $this;

		return $props;
	}

	public static function factory(string|array $path): static
	{
		if (is_string($path) === true) {
			return new static(path: $path);
		}

		return new static(...$path);
	}

	public function read(): array
	{
		$config = new Config($this->path);
		return $config->read();
	}
}
