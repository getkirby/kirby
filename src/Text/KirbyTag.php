<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Reflection\Constructor;
use Kirby\Uuid\Uri as UuidUri;
use Kirby\Uuid\Uuid;

/**
 * Represents and parses a single KirbyTag.
 *
 * Each tag type is implemented as a subclass.
 * Its attributes are declared as named constructor arguments.
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
	public array $data = [];
	public string $type = '';
	public string|null $value = null;

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
	public static function __callStatic(
		string $type,
		array $arguments = []
	): string {
		return static::factory($type, ...$arguments)->render();
	}

	public function attr(string $name, $default = null)
	{
		$name = strtolower($name);
		return $this->$name ?? $default;
	}

	/**
	 * Returns the list of attribute names this tag type supports.
	 *
	 * @since 6.0.0
	 */
	public static function attrs(): array
	{
		// Deriving the list of attributes from
		// the named constructor arguments
		return static::$attrsCache[static::class] ??=
			Constructor::for(static::class)?->getParameterNames() ?? [];
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
	 * Injects shared, non-attribute state after the tag
	 * instance has been created from its attribute arguments
	 * @since 6.0.0
	 */
	protected function bind(
		string $type,
		string|null $value,
		array $data,
		array $attrs
	): static {
		$this->type  = $type;
		$this->value = $value;
		$this->data  = $data;
		$this->attrs = $attrs;
		return $this;
	}

	/**
	 * Creates a new tag instance for the given type
	 * @since 6.0.0
	 */
	final public static function factory(
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

		// merge the per-type option defaults and normalize the
		// attribute keys, so they can be matched against the
		// (lowercase) constructor argument names
		$kirby    = $data['kirby'] ?? App::instance();
		$defaults = $kirby->option('kirbytext.' . $type, []);
		$attrs    = array_change_key_case(array_replace($defaults, $attrs));

		// legacy array definitions are wrapped, class-based definitions
		// receive their attributes as named constructor arguments;
		// the shared state is injected afterwards via `bind()`
		$tag = match (true) {
			is_array($tag) => new LegacyKirbyTag($tag),
			default        => new $tag(
				...(Constructor::for($tag)?->getAcceptedArguments($attrs) ?? [])
			)
		};

		return $tag->bind($type, $value, $data, $attrs);
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
