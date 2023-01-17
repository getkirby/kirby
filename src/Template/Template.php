<?php

namespace Kirby\Template;

use Exception;
use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Tpl;

/**
 * Represents a Kirby template and takes care
 * of loading the correct file.
 *
 * @package   Kirby Template
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Template
{
	/**
	 * Global template data
	 */
	public static array $data = [];

	/**
	 * Default template type if no specific type is set
	 */
	protected string $defaultType;

	/**
	 * The name of the template
	 */
	protected string $name;

	/**
	 * Template type (html, json, etc.)
	 */
	protected string $type;

	/**
	 * Creates a new template object
	 */
	public function __construct(string $name, string $type = 'html', string $defaultType = 'html')
	{
		$this->name = strtolower($name);
		$this->type = $type;
		$this->defaultType = $defaultType;
	}

	/**
	 * Converts the object to a simple string
	 * This is used in template filters for example
	 */
	public function __toString(): string
	{
		return $this->name;
	}

	/**
	 * Returns the default template type
	 */
	public function defaultType(): string
	{
		return $this->defaultType;
	}

	/**
	 * Checks if the template exists
	 */
	public function exists(): bool
	{
		if ($file = $this->file()) {
			return file_exists($file);
		}

		return false;
	}

	/**
	 * Returns the expected template file extension
	 */
	public function extension(): string
	{
		return 'php';
	}

	/**
	 * Detects the location of the template file
	 * if it exists.
	 */
	public function file(): string|null
	{
		$name      = $this->name();
		$extension = $this->extension();
		$store     = $this->store();
		$root      = $this->root();

		if ($this->hasDefaultType() === true) {
			try {
				// Try the default template in the default template directory.
				return F::realpath($root . '/' . $name . '.' . $extension, $root);
			} catch (Exception) {
				// ignore errors, continue searching
			}

			// Look for the default template provided by an extension.
			$path = App::instance()->extension($store, $name);

			if ($path !== null) {
				return $path;
			}
		}

		$name .= '.' . $this->type();

		try {
			// Try the template with type extension in the default template directory.
			return F::realpath($root . '/' . $name . '.' . $extension, $root);
		} catch (Exception) {
			// Look for the template with type extension provided by an extension.
			// This might be null if the template does not exist.
			return App::instance()->extension($store, $name);
		}
	}

	/**
	 * Checks if the template uses the default type
	 */
	public function hasDefaultType(): bool
	{
		return $this->type() === $this->defaultType();
	}

	/**
	 * Returns the template name
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Renders the template with the given template data
	 */
	public function render(array $data = []): string
	{
		// if the template is rendered inside a snippet,
		// we need to keep the "outside" snippet object
		// to compare it later
		$snippet = Snippet::$current;

		// load the template
		$template = Tpl::load($this->file(), $data);

		// if last `endsnippet()` inside the current template
		// has been omitted (= snippet was used as layout snippet),
		// `Snippet::$current` will point to a snippet that was
		// opened inside the template; if that snippet is the direct
		// child of the snippet that was open before the template was
		// rendered (which could be `null` if no snippet was open),
		// take the buffer output from the template as default slot
		// and render the snippet as final template output
		if (
			Snippet::$current === null ||
			Snippet::$current->parent() !== $snippet
		) {
			return $template;
		}

		// no slots have been defined, but the template code
		// should be used as default slot
		if (Snippet::$current->slots()->count() === 0) {
			return Snippet::$current->render($data, [
				'default' => $template
			]);
		}

		// let the snippet close and render natively
		return Snippet::$current->render($data);
	}

	/**
	 * Returns the root to the templates directory
	 */
	public function root(): string
	{
		return App::instance()->root($this->store());
	}

	/**
	 * Returns the place where templates are located
	 * in the site folder and and can be found in extensions
	 */
	public function store(): string
	{
		return 'templates';
	}

	/**
	 * Returns the template type
	 */
	public function type(): string
	{
		return $this->type;
	}
}
