<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Uuid\Uri as UuidUri;
use Kirby\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;

/**
 * Represents and parses a single KirbyTag.
 *
 * Each tag type is implemented as a subclass.
 * Custom tags registered in the legacy array form
 * are wrapped in a `Kirby\Text\LegacyKirbyTag` instance.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class KirbyTag
{
	/**
	 * @var array<string, string>
	 */
	public static array $aliases = [];
	protected static array $attrsCache = [];

	/**
	 * Registry of all available tag types
	 * @var array<string, array|class-string<self>>
	 */
	public static array $types = [];

	public array $attrs = [];

	public function __construct(
		public string $type,
		public string|null $value = null,
		array $attrs = [],
		public array $data = []
	) {
		$defaults    = $this->kirby()->option('kirbytext.' . $type, []);
		$this->attrs = array_replace($defaults, $attrs);

		// only keep attributes that the tag type actually defines
		$availableAttrs = $this->definedAttrs();

		foreach ($this->attrs as $attrName => $attrValue) {
			$attrName = strtolower($attrName);

			if (in_array($attrName, $availableAttrs, true) === true) {
				$this->$attrName = $attrValue;
			}
		}

		// type aliases
		if (isset(static::$types[$type]) === false) {
			if (isset(static::$aliases[$type]) === false) {
				throw new InvalidArgumentException(
					message: 'Undefined tag type: ' . $type
				);
			}

			$type = static::$aliases[$type];
		}
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
		return static::factory($type, ...$arguments)->render();
	}

	public function attr(string $name, $default = null)
	{
		$name = strtolower($name);
		return $this->$name ?? $default;
	}

	/**
	 * Returns the list of attribute names this tag type supports,
	 * derived from the public, non-static properties declared
	 * on the concrete tag class
	 *
	 * @since 6.0.0
	 */
	public static function attrs(): array
	{
		if (isset(static::$attrsCache[static::class]) === true) {
			return static::$attrsCache[static::class];
		}

		$class = new ReflectionClass(static::class);
		$props = $class->getProperties(ReflectionProperty::IS_PUBLIC);
		$props = array_filter(
			$props,
			fn (ReflectionProperty $prop): bool =>
				$prop->isStatic() === false &&
				$prop->getDeclaringClass()->getName() !== self::class
		);

		$attrs = array_map(
			fn (ReflectionProperty $prop): string => $prop->getName(),
			$props
		);

		return static::$attrsCache[static::class] = array_values($attrs);
	}

	/**
	 * Returns the supported attribute names for a registered type
	 * before an instance exists (used while parsing the raw tag).
	 *
	 * @since 6.0.0
	 */
	protected static function attrsFor(string $type): array
	{
		$definition = static::$types[$type] ?? null;

		return match (true) {
			is_string($definition) => $definition::attrs(),
			is_array($definition)  => $definition['attr'] ?? [],
			default                => []
		};
	}

	/**
	 * Returns the attribute names the current instance accepts.
	 * `LegacyKirbyTag` overrides this to read from its definition.
	 *
	 * @since 6.0.0
	 */
	protected function definedAttrs(): array
	{
		return static::attrs();
	}

	/**
	 * Creates a new tag instance for the given type
	 */
	public static function factory(
		string $type,
		string|null $value = null,
		array $attrs = [],
		array $data = []
	): static {
		// resolve type aliases
		if (isset(static::$types[$type]) === false) {
			if (isset(static::$aliases[$type]) === false) {
				throw new InvalidArgumentException(
					message: 'Undefined tag type: ' . $type
				);
			}

			$type = static::$aliases[$type];
		}

		$tag = static::$types[$type];

		// legacy array definition
		if (is_array($tag) === true) {
			return new LegacyKirbyTag($tag, $type, $value, $attrs, $data);
		}

		// class-based tag definition
		return new $tag($type, $value, $attrs, $data);
	}

	/**
	 * Finds a file for the given path.
	 * The method first searches the file
	 * in the current parent, if it's a page.
	 * Afterwards it uses Kirby's global file finder.
	 */
	public function file(string $path): File|null
	{
		$parent = $this->parent();

		// check first for UUID
		if (Uuid::is($path, 'file') === true) {
			if ($parent && method_exists($parent, 'files') === true) {
				$context = $parent->files();
			}

			return Uuid::from($path, context: $context ?? null)->model();
		}

		if (
			$parent &&
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

		return $this->kirby()->file($path, null, true);
	}

	/**
	 * Returns the current Kirby instance
	 */
	public function kirby(): App
	{
		return $this->data['kirby'] ?? App::instance();
	}

	/**
	 * Returns the parent model
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->data['parent'] ?? null;
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

		$pos   = strpos($tag, ':');
		$type  = trim(substr($tag, 0, $pos ?: null));
		$type  = strtolower($type);
		$attrs = static::attrsFor($type);

		// the type should be parsed as an attribute, so we add it here
		// to the list of possible attributes
		array_unshift($attrs, $type);

		// ensure that UUIDs protocols aren't matched as attributes
		$uuids = sprintf('(?!(%s):\/\/)', implode('|', UuidUri::$schemes));

		// extract all attributes
		$regex  = sprintf('/%s(%s):/i', $uuids, implode('|', $attrs));
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

		// combine the two arrays to an associative array
		$attrs = array_combine($keys, $values);

		// the first attribute is the type attribute
		// extract and pass its value separately
		$value = array_shift($attrs);

		return static::factory($type, $value, $attrs, $data);
	}

	abstract public function render(): string;

	public function type(): string
	{
		return $this->type;
	}
}
