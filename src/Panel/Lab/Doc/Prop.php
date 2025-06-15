<?php

namespace Kirby\Panel\Lab\Doc;

use Kirby\Panel\Lab\Doc;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Documentation for a single Vue component prop
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 * @codeCoverageIgnore
 */
class Prop
{
	public function __construct(
		public string $name,
		public string|null $type = null,
		public string|null $description = null,
		public string|null $default = null,
		public string|null $deprecated = null,
		public string|null $example = null,
		public bool $required = false,
		public string|null $since = null,
		public string|null $value = null,
		public array $values = []
	) {
		$this->description = Doc::kt($this->description ?? '');
		$this->deprecated  = Doc::kt($this->deprecated ?? '');
	}

	public static function factory(array $data): static|null
	{
		// filter internal props
		if (isset($data['tags']['internal']) === true) {
			return null;
		}

		// filter unset props
		if (($type = $data['type']['name'] ?? null) === 'null') {
			return null;
		}

		return new static(
			name:        $data['name'],
			type:        $type,
			default:     self::normalizeDefault($data['defaultValue']['value'] ?? null, $type),
			description: $data['description'] ?? null,
			deprecated:  $data['tags']['deprecated'][0]['description'] ?? null,
			example:     $data['tags']['example'][0]['description'] ?? null,
			required:    $data['required'] ?? false,
			since:       $data['tags']['since'][0]['description'] ?? null,
			value:       $data['tags']['value'][0]['description'] ?? null,
			values:      $data['values'] ?? []
		);
	}

	protected static function normalizeDefault(
		string|null $default,
		string|null $type
	): string|null {
		if ($default === null) {
			// if type is boolean primarily and no default
			// value has been set, add `false` as default
			// for clarity
			if (Str::startsWith($type, 'boolean')) {
				return 'false';
			}

			return null;
		}

		// normalize longform function
		if (preg_match('/function\(\) {.*return (.*);.*}/si', $default, $matches) === 1) {
			return $matches[1];
		}

		// normalize object shorthand function
		if (preg_match('/\(\) => \((.*)\)/si', $default, $matches) === 1) {
			return $matches[1];
		}

		// normalize all other defaults from shorthand function
		if (preg_match('/\(\) => (.*)/si', $default, $matches) === 1) {
			return $matches[1];
		}

		return $default;
	}

	public function toArray(): array
	{
		return [
			'name'        => $this->name,
			'default'     => $this->default,
			'description' => $this->description,
			'deprecated'  => $this->deprecated,
			'example'     => $this->example,
			'required'    => $this->required,
			'since'       => $this->since,
			'type'        => $this->type,
			'value'       => $this->value,
			'values'      => $this->values,
		];
	}
}
