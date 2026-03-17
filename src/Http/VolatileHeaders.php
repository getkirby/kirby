<?php

namespace Kirby\Http;

use Kirby\Cms\Cors;
use Kirby\Toolkit\A;

/**
 * Manages request-dependent headers that must not be
 * persisted in cached responses
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.3.0
 */
class VolatileHeaders
{
	/**
	 * Stored volatile header configurations
	 */
	protected array $headers = [];

	/**
	 * Adds (parts of) a header to the volatile list
	 */
	protected function append(
		string $name,
		array|null $values = null,
		array|null &$target = null
	): void {
		if ($values === null) {
			$target[$name] = null;
			return;
		}

		if (array_key_exists($name, $target) === true && $target[$name] === null) {
			return;
		}

		$values = A::map($values, static fn ($value) => strtolower(trim($value)));
		$values = A::filter($values, static fn ($value) => $value !== '');

		if ($values === []) {
			return;
		}

		$existingValues = $target[$name] ?? [];
		$target[$name] = array_values(array_unique([...$existingValues, ...$values]));
	}

	/**
	 * Collects all volatile headers including CORS headers
	 */
	public function collect(): array
	{
		$volatile = $this->headers;
		$corsHeaders = Cors::headers();

		if ($corsHeaders === []) {
			return $volatile;
		}

		foreach ($corsHeaders as $name => $value) {
			if ($name === 'Vary') {
				$corsVaryValues = array_map('trim', explode(',', $value));
				$this->append($name, $corsVaryValues, $volatile);
				continue;
			}

			$this->append($name, null, $volatile);
		}

		return $volatile;
	}

	/**
	 * Marks headers (or header parts) as request-dependent
	 */
	public function mark(string $name, array|null $values = null): void
	{
		$this->append($name, $values, $this->headers);
	}

	/**
	 * Normalizes a comma-separated list of Vary values
	 * into a unique array without empty entries
	 */
	protected function normalizeVaryValues(string $value): array
	{
		$values = A::map(explode(',', $value), 'trim');
		$values = A::filter($values, static fn ($entry) => $entry !== '');

		return array_values(array_unique($values));
	}

	/**
	 * Returns the Vary values with the provided entries removed
	 */
	protected function removeVaryValues(array $values, array $remove): array
	{
		$removeLower = A::map($remove, 'strtolower');

		return array_values(A::filter(
			$values,
			static fn ($value) => in_array(strtolower($value), $removeLower, true) === false
		));
	}

	/**
	 * Strips volatile headers from the provided header array
	 */
	public function strip(array $headers, array|null $volatile = null): array
	{
		$volatile ??= $this->collect();

		foreach ($volatile as $name => $values) {
			if ($name === 'Vary' && is_array($values) === true) {
				$headers = $this->stripVaryHeader($headers, $values);
				continue;
			}

			unset($headers[$name]);
		}

		return $headers;
	}

	/**
	 * Strips Vary header values from the headers array
	 */
	protected function stripVaryHeader(array $headers, array $values): array
	{
		if (isset($headers['Vary']) === false) {
			return $headers;
		}

		$current = $this->normalizeVaryValues($headers['Vary']);
		$remaining = $this->removeVaryValues($current, $values);

		if ($remaining === []) {
			unset($headers['Vary']);
		} else {
			$headers['Vary'] = implode(', ', $remaining);
		}

		return $headers;
	}
}
