<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Filesystem\IsFile;
use Kirby\Panel\File as Panel;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * The `$file` object provides a set
 * of methods that can be used when
 * dealing with a single image or
 * other media file, like getting the
 * URL or resizing an image. It also
 * handles file meta data.
 *
 * The File class proxies the `Kirby\Filesystem\File`
 * or `Kirby\Image\Image` class, which
 * is used to handle all asset file methods.
 * In addition the File class handles
 * meta data via `Kirby\Cms\Content`.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class File extends ModelWithContent
{
	use FileActions;
	use FileModifications;
	use HasMethods;
	use HasSiblings;
	use IsFile;

	public const CLASS_ALIAS = 'file';

	/**
	 * Cache for the initialized blueprint object
	 *
	 * @var \Kirby\Cms\FileBlueprint
	 */
	protected $blueprint;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * All registered file methods
	 *
	 * @var array
	 */
	public static $methods = [];

	/**
	 * The parent object
	 *
	 * @var \Kirby\Cms\Model
	 */
	protected $parent;

	/**
	 * The absolute path to the file
	 */
	protected string|null $root = null;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * The public file Url
	 */
	protected string|null $url = null;

	/**
	 * Magic caller for file methods
	 * and content fields. (in this order)
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $method, array $arguments = [])
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		// asset method proxy
		if (method_exists($this->asset(), $method)) {
			return $this->asset()->$method(...$arguments);
		}

		// file methods
		if ($this->hasMethod($method)) {
			return $this->callMethod($method, $arguments);
		}

		// content fields
		return $this->content()->get($method);
	}

	/**
	 * Creates a new File object
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		// set filename as the most important prop first
		// TODO: refactor later to avoid redundant prop setting
		$this->setProperty('filename', $props['filename'] ?? null, true);

		// set other properties
		$this->setProperties($props);
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return array_merge($this->toArray(), [
			'content'  => $this->content(),
			'siblings' => $this->siblings(),
		]);
	}

	/**
	 * Returns the url to api endpoint
	 *
	 * @internal
	 * @param bool $relative
	 * @return string
	 */
	public function apiUrl(bool $relative = false): string
	{
		return $this->parent()->apiUrl($relative) . '/files/' . $this->filename();
	}

	/**
	 * Returns the FileBlueprint object for the file
	 *
	 * @return \Kirby\Cms\FileBlueprint
	 */
	public function blueprint()
	{
		if ($this->blueprint instanceof FileBlueprint) {
			return $this->blueprint;
		}

		return $this->blueprint = FileBlueprint::factory('files/' . $this->template(), 'files/default', $this);
	}

	/**
	 * Store the template in addition to the
	 * other content.
	 *
	 * @internal
	 * @param array $data
	 * @param string|null $languageCode
	 * @return array
	 */
	public function contentFileData(array $data, string $languageCode = null): array
	{
		return A::append($data, [
			'template' => $this->template(),
		]);
	}

	/**
	 * Returns the directory in which
	 * the content file is located
	 *
	 * @internal
	 * @return string
	 */
	public function contentFileDirectory(): string
	{
		return dirname($this->root());
	}

	/**
	 * Filename for the content file
	 *
	 * @internal
	 * @return string
	 */
	public function contentFileName(): string
	{
		return $this->filename();
	}

	/**
	 * Constructs a File object
	 *
	 * @internal
	 * @param mixed $props
	 * @return static
	 */
	public static function factory($props)
	{
		return new static($props);
	}

	/**
	 * Returns the filename with extension
	 *
	 * @return string
	 */
	public function filename(): string
	{
		return $this->filename;
	}

	/**
	 * Returns the parent Files collection
	 *
	 * @return \Kirby\Cms\Files
	 */
	public function files()
	{
		return $this->siblingsCollection();
	}

	/**
	 * Converts the file to html
	 *
	 * @param array $attr
	 * @return string
	 */
	public function html(array $attr = []): string
	{
		return $this->asset()->html(array_merge(
			['alt' => $this->alt()],
			$attr
		));
	}

	/**
	 * Returns the id
	 *
	 * @return string
	 */
	public function id(): string
	{
		if ($this->id !== null) {
			return $this->id;
		}

		if (
			$this->parent() instanceof Page ||
			$this->parent() instanceof User
		) {
			return $this->id = $this->parent()->id() . '/' . $this->filename();
		}

		return $this->id = $this->filename();
	}

	/**
	 * Compares the current object with the given file object
	 *
	 * @param \Kirby\Cms\File $file
	 * @return bool
	 */
	public function is(File $file): bool
	{
		return $this->id() === $file->id();
	}

	/**
	 * Check if the file can be read by the current user
	 *
	 * @return bool
	 */
	public function isReadable(): bool
	{
		static $readable = [];

		$template = $this->template();

		if (isset($readable[$template]) === true) {
			return $readable[$template];
		}

		return $readable[$template] = $this->permissions()->can('read');
	}

	/**
	 * Creates a unique media hash
	 *
	 * @internal
	 * @return string
	 */
	public function mediaHash(): string
	{
		return $this->mediaToken() . '-' . $this->modifiedFile();
	}

	/**
	 * Returns the absolute path to the file in the public media folder
	 *
	 * @internal
	 * @return string
	 */
	public function mediaRoot(): string
	{
		return $this->parent()->mediaRoot() . '/' . $this->mediaHash() . '/' . $this->filename();
	}

	/**
	 * Creates a non-guessable token string for this file
	 *
	 * @internal
	 * @return string
	 */
	public function mediaToken(): string
	{
		$token = $this->kirby()->contentToken($this, $this->id());
		return substr($token, 0, 10);
	}

	/**
	 * Returns the absolute Url to the file in the public media folder
	 *
	 * @internal
	 * @return string
	 */
	public function mediaUrl(): string
	{
		return $this->parent()->mediaUrl() . '/' . $this->mediaHash() . '/' . $this->filename();
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @param string|\IntlDateFormatter|null $format
	 * @param string|null $handler date, intl or strftime
	 * @param string|null $languageCode
	 * @return mixed
	 */
	public function modified($format = null, string $handler = null, string $languageCode = null)
	{
		$file     = $this->modifiedFile();
		$content  = $this->modifiedContent($languageCode);
		$modified = max($file, $content);
		$handler ??= $this->kirby()->option('date.handler', 'date');

		return Str::date($modified, $format, $handler);
	}

	/**
	 * Timestamp of the last modification
	 * of the content file
	 *
	 * @param string|null $languageCode
	 * @return int
	 */
	protected function modifiedContent(string $languageCode = null): int
	{
		return F::modified($this->contentFile($languageCode));
	}

	/**
	 * Timestamp of the last modification
	 * of the source file
	 *
	 * @return int
	 */
	protected function modifiedFile(): int
	{
		return F::modified($this->root());
	}

	/**
	 * Returns the parent Page object
	 *
	 * @return \Kirby\Cms\Page|null
	 */
	public function page()
	{
		if ($this->parent() instanceof Page) {
			return $this->parent();
		}

		return null;
	}

	/**
	 * Returns the panel info object
	 *
	 * @return \Kirby\Panel\File
	 */
	public function panel()
	{
		return new Panel($this);
	}

	/**
	 * Returns the parent Model object
	 *
	 * @return \Kirby\Cms\Model
	 */
	public function parent()
	{
		return $this->parent ??= $this->kirby()->site();
	}

	/**
	 * Returns the parent id if a parent exists
	 *
	 * @internal
	 * @return string
	 */
	public function parentId(): string
	{
		return $this->parent()->id();
	}

	/**
	 * Returns a collection of all parent pages
	 *
	 * @return \Kirby\Cms\Pages
	 */
	public function parents()
	{
		if ($this->parent() instanceof Page) {
			return $this->parent()->parents()->prepend($this->parent()->id(), $this->parent());
		}

		return new Pages();
	}

	/**
	 * Return the permanent URL to the file using its UUID
	 * @since 3.8.0
	 */
	public function permalink(): string
	{
		return $this->uuid()->url();
	}

	/**
	 * Returns the permissions object for this file
	 *
	 * @return \Kirby\Cms\FilePermissions
	 */
	public function permissions()
	{
		return new FilePermissions($this);
	}

	/**
	 * Returns the absolute root to the file
	 *
	 * @return string|null
	 */
	public function root(): string|null
	{
		return $this->root ??= $this->parent()->root() . '/' . $this->filename();
	}

	/**
	 * Returns the FileRules class to
	 * validate any important action.
	 *
	 * @return \Kirby\Cms\FileRules
	 */
	protected function rules()
	{
		return new FileRules();
	}

	/**
	 * Sets the Blueprint object
	 *
	 * @param array|null $blueprint
	 * @return $this
	 */
	protected function setBlueprint(array $blueprint = null)
	{
		if ($blueprint !== null) {
			$blueprint['model'] = $this;
			$this->blueprint = new FileBlueprint($blueprint);
		}

		return $this;
	}

	/**
	 * Sets the filename
	 *
	 * @param string $filename
	 * @return $this
	 */
	protected function setFilename(string $filename)
	{
		$this->filename = $filename;
		return $this;
	}

	/**
	 * Sets the parent model object
	 *
	 * @param \Kirby\Cms\Model $parent
	 * @return $this
	 */
	protected function setParent(Model $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Always set the root to null, to invoke
	 * auto root detection
	 *
	 * @param string|null $root
	 * @return $this
	 */
	protected function setRoot(string $root = null)
	{
		$this->root = null;
		return $this;
	}

	/**
	 * @param string|null $template
	 * @return $this
	 */
	protected function setTemplate(string $template = null)
	{
		$this->template = $template;
		return $this;
	}

	/**
	 * Sets the url
	 *
	 * @param string|null $url
	 * @return $this
	 */
	protected function setUrl(string $url = null)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Returns the parent Files collection
	 * @internal
	 *
	 * @return \Kirby\Cms\Files
	 */
	protected function siblingsCollection()
	{
		return $this->parent()->files();
	}

	/**
	 * Returns the parent Site object
	 *
	 * @return \Kirby\Cms\Site
	 */
	public function site()
	{
		if ($this->parent() instanceof Site) {
			return $this->parent();
		}

		return $this->kirby()->site();
	}

	/**
	 * Returns the final template
	 *
	 * @return string|null
	 */
	public function template(): string|null
	{
		return $this->template ??= $this->content()->get('template')->value();
	}

	/**
	 * Returns siblings with the same template
	 *
	 * @param bool $self
	 * @return \Kirby\Cms\Files
	 */
	public function templateSiblings(bool $self = true)
	{
		return $this->siblings($self)->filter('template', $this->template());
	}

	/**
	 * Extended info for the array export
	 * by injecting the information from
	 * the asset.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return array_merge($this->asset()->toArray(), parent::toArray());
	}

	/**
	 * Returns the Url
	 *
	 * @return string
	 */
	public function url(): string
	{
		return $this->url ??= ($this->kirby()->component('file::url'))($this->kirby(), $this);
	}

	/**
	 * Simplified File URL that uses the parent
	 * Page URL and the filename as a more stable
	 * alternative for the media URLs.
	 *
	 * @return string
	 */
	public function previewUrl(): string
	{
		$parent = $this->parent();
		$url    = Url::to($this->id());

		switch ($parent::CLASS_ALIAS) {
			case 'page':
				$preview = $parent->blueprint()->preview();

				// the page has a custom preview setting,
				// thus the file is only accessible through
				// the direct media URL
				if ($preview !== true) {
					return $this->url();
				}

				// it's more stable to access files for drafts
				// through their direct URL to avoid conflicts
				// with draft token verification
				if ($parent->isDraft() === true) {
					return $this->url();
				}

				// checks `file::url` component is extended
				if ($this->kirby()->isNativeComponent('file::url') === false) {
					return $this->url();
				}

				return $url;
			case 'user':
				return $this->url();
			default:
				return $url;
		}
	}
}
