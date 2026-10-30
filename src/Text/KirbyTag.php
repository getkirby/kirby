<?php

namespace Kirby\Text;

use AllowDynamicProperties;
use Closure;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Uuid\Uri as UuidUri;
use Kirby\Uuid\Uuid;

/**
 * Representation and parse of a single KirbyTag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
#[AllowDynamicProperties]
class KirbyTag
{
	/**
	 * Alias names and the tag types they point to
	 *
	 * @var array<string, string>
	 */
	public static array $aliases = [];

	/**
	 * Tag definitions with their `attr` list and `html` callback
	 *
	 * @var array<string, array<string, mixed>>
	 */
	public static array $types = [];

	/**
	 * @var array<string, mixed>
	 */
	public array $attrs = [];

	/**
	 * @var array<string, mixed>
	 */
	public array $data = [];
	public string $type;
	public string|null $value = null;

	public function __construct(
		string $type,
		string|null $value = null,
		array $attrs = [],
		array $data = []
	) {
		// type aliases
		if (isset(static::$types[$type]) === false) {
			if (isset(static::$aliases[$type]) === false) {
				throw new InvalidArgumentException(
					message: 'Undefined tag type: ' . $type
				);
			}

			$type = static::$aliases[$type];
		}

		$kirby    = $data['kirby'] ?? App::instance();
		$defaults = $kirby->option('kirbytext.' . $type, []);
		$attrs    = array_replace($defaults, $attrs);

		// all available tag attributes
		$availableAttrs = static::$types[$type]['attr'] ?? [];

		foreach ($attrs as $attrName => $attrValue) {
			$attrName = strtolower($attrName);

			// applies only defined attributes to safely update
			if (in_array($attrName, $availableAttrs, true) === true) {
				$this->{$attrName} = $attrValue;
			}
		}

		$this->attrs = $attrs;
		$this->data  = $data;
		$this->$type = $value;
		$this->type  = $type;
		$this->value = $value;
	}

	/**
	 * Magic data and property getter
	 */
	public function __call(string $name, array $arguments = [])
	{
		return $this->data[$name] ?? $this->$name;
	}

	/**
	 * Magic call `KirbyTag::myType($parameter1, $parameter2)`
	 */
	public static function __callStatic(string $type, array $arguments = []): string
	{
		return new static($type, ...$arguments)->render();
	}

	public function __get(string $attr)
	{
		$attr = strtolower($attr);
		return $this->$attr ?? null;
	}

	public function __set(string $name, mixed $value): void
	{
		$this->{strtolower($name)} = $value;
	}

	public function attr(string $name, $default = null)
	{
		$name = strtolower($name);
		return $this->$name ?? $default;
	}

	public static function factory(...$arguments): string
	{
		return new static(...$arguments)->render();
	}

	/**
	 * Finds a file for the given path or UUID.
	 * UUIDs are looked up in the files of the parent first.
	 * Paths are searched in the parent, then in the page of
	 * a file parent and finally with Kirby's global file finder.
	 */
	public function file(string $path): File|null
	{
		$parent = $this->parent();

		if (Uuid::is($path, 'file') === true) {
			if ($parent !== null && method_exists($parent, 'files') === true) {
				$context = $parent->files();
			}

			return Uuid::from($path, context: $context ?? null)->model();
		}

		if (
			$parent !== null &&
			method_exists($parent, 'file') === true &&
			$file = $parent->file($path)
		) {
			return $file;
		}

		if (
			$parent instanceof File &&
			$file = $parent->page()?->file($path)
		) {
			return $file;
		}

		return $this->kirby()->file($path);
	}

	/**
	 * Returns the current Kirby instance
	 */
	public function kirby(): App
	{
		return $this->data['kirby'] ?? App::instance();
	}

	public static function parse(
		string $string,
		array $data = []
	): static {
		// remove the brackets, extract the first attribute (the tag type)
		$tag = trim(ltrim($string, '('));

		// use substr instead of rtrim to keep non-tagged brackets
		// (link: file.pdf text: Download (PDF))
		if (str_ends_with($tag, ')') === true) {
			$tag = substr($tag, 0, -1);
		}

		$pos  = strpos($tag, ':');
		$type = trim(substr($tag, 0, $pos ?: null));
		$type = strtolower($type);
		$attr = static::$types[$type]['attr'] ?? [];

		// the type should be parsed as an attribute, so we add it here
		// to the list of possible attributes
		array_unshift($attr, $type);

		// ensure that UUIDs protocols aren't matched as attributes
		$uuids = sprintf('(?!(%s):\/\/)', implode('|', UuidUri::$schemes));

		$regex = sprintf('/%s(%s):/i', $uuids, implode('|', $attr));
		$search = preg_split($regex, $tag, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// $search is now an array with alternating keys and values
		// convert it to arrays of keys and values
		$chunks = array_chunk($search, 2);
		$keys   = array_column($chunks, 0);
		$values = array_map(trim(...), array_column($chunks, 1));

		// ensure that there is a value for each key
		// otherwise combining won't work
		if (count($values) < count($keys)) {
			$values[] = '';
		}

		$attributes = array_combine($keys, $values);

		// the first attribute is the type attribute
		// extract and pass its value separately
		$value = array_shift($attributes);

		return new static($type, $value, $attributes, $data);
	}

	/**
	 * Returns the parent model
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->data['parent'] ?? null;
	}

	public function render(): string
	{
		$callback = static::$types[$this->type]['html'] ?? null;

		if ($callback instanceof Closure) {
			return (string)$callback($this);
		}

		throw new BadMethodCallException(
			message: 'Invalid tag render function in tag: ' . $this->type
		);
	}

	public function type(): string
	{
		return $this->type;
	}
}
