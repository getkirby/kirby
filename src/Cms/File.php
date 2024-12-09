<?php

namespace Kirby\Cms;

use Exception;
use IntlDateFormatter;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\IsFile;
use Kirby\Panel\File as Panel;
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
	 * All registered file methods
	 * @todo Remove when support for PHP 8.2 is dropped
	 */
	public static array $methods = [];

	/**
	 * Cache for the initialized blueprint object
	 */
	protected FileBlueprint|null $blueprint = null;

	protected string $filename;

	protected string $id;

	/**
	 * The parent object
	 */
	protected Page|Site|User|null $parent = null;

	/**
	 * The absolute path to the file
	 */
	protected string|null $root;

	protected string|null $template;

	/**
	 * The public file Url
	 */
	protected string|null $url;

	/**
	 * Creates a new File object
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		if (isset($props['filename'], $props['parent']) === false) {
			throw new InvalidArgumentException('The filename and parent are required');
		}

		$this->filename = $props['filename'];
		$this->parent   = $props['parent'];
		$this->template = $props['template'] ?? null;
		// Always set the root to null, to invoke
		// auto root detection
		$this->root     = null;
		$this->url      = $props['url'] ?? null;

		$this->setBlueprint($props['blueprint'] ?? null);
	}

	/**
	 * Magic caller for file methods
	 * and content fields. (in this order)
	 */
	public function __call(string $method, array $arguments = []): mixed
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
	 * Improved `var_dump` output
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
	 * @internal
	 */
	public function apiUrl(bool $relative = false): string
	{
		return $this->parent()->apiUrl($relative) . '/files/' . $this->filename();
	}

	/**
	 * Returns the FileBlueprint object for the file
	 */
	public function blueprint(): FileBlueprint
	{
		return $this->blueprint ??= FileBlueprint::factory(
			'files/' . $this->template(),
			'files/default',
			$this
		);
	}

	/**
	 * Returns an array with all blueprints that are available for the file
	 * by comparing files sections and files fields of the parent model
	 */
	public function blueprints(string|null $inSection = null): array
	{
		// get cached results for the current file model
		// (except when collecting for a specific section)
		if ($inSection === null && $this->blueprints !== null) {
			return $this->blueprints; // @codeCoverageIgnore
		}

		// always include the current template as option
		$templates = [
			$this->template() ?? 'default',
			...$this->parent()->blueprint()->acceptedFileTemplates($inSection)
		];

		// make sure every template is only included once
		$templates = array_unique(array_filter($templates));

		// load the blueprint details for each collected template name
		$blueprints = [];

		foreach ($templates as $template) {
			// default template doesn't need to exist as file
			// to be included in the list
			if ($template === 'default') {
				$blueprints[$template] = [
					'name'  => 'default',
					'title' => '– (default)',
				];
				continue;
			}

			if ($blueprint = FileBlueprint::factory('files/' . $template, null, $this)) {
				try {
					// ensure that file matches `accept` option,
					// if not remove template from available list
					$this->match($blueprint->accept());

					$blueprints[$template] = [
						'name'  => $name = Str::after($blueprint->name(), '/'),
						'title' => $blueprint->title() . ' (' . $name . ')',
					];
				} catch (Exception) {
					// skip when `accept` doesn't match
				}
			}
		}

		$blueprints = array_values($blueprints);

		// sort blueprints alphabetically while
		// making sure the default blueprint is on top of list
		usort($blueprints, fn ($a, $b) => match (true) {
			$a['name'] === 'default' => -1,
			$b['name'] === 'default' => 1,
			default => strnatcmp($a['title'], $b['title'])
		});

		// no caching for when collecting for specific section
		if ($inSection !== null) {
			return $blueprints; // @codeCoverageIgnore
		}

		return $this->blueprints = $blueprints;
	}

	/**
	 * Store the template in addition to the
	 * other content.
	 * @internal
	 */
	public function contentFileData(
		array $data,
		string|null $languageCode = null
	): array {
		// only add the template in, if the $data array
		// doesn't explicitly unsets it
		if (
			array_key_exists('template', $data) === false &&
			$template = $this->template()
		) {
			$data['template'] = $template;
		}

		return $data;
	}

	/**
	 * Returns the directory in which
	 * the content file is located
	 * @internal
	 * @deprecated 4.0.0
	 * @todo Remove in v5
	 * @codeCoverageIgnore
	 */
	public function contentFileDirectory(): string
	{
		Helpers::deprecated('The internal $model->contentFileDirectory() method has been deprecated. Please let us know via a GitHub issue if you need this method and tell us your use case.', 'model-content-file');
		return dirname($this->root());
	}

	/**
	 * Filename for the content file
	 * @internal
	 * @deprecated 4.0.0
	 * @todo Remove in v5
	 * @codeCoverageIgnore
	 */
	public function contentFileName(): string
	{
		Helpers::deprecated('The internal $model->contentFileName() method has been deprecated. Please let us know via a GitHub issue if you need this method and tell us your use case.', 'model-content-file');
		return $this->filename();
	}

	/**
	 * Constructs a File object
	 * @internal
	 */
	public static function factory(array $props): static
	{
		return new static($props);
	}

	/**
	 * Returns the filename with extension
	 */
	public function filename(): string
	{
		return $this->filename;
	}

	/**
	 * Returns the parent Files collection
	 */
	public function files(): Files
	{
		return $this->siblingsCollection();
	}

	/**
	 * Converts the file to html
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
	 */
	public function id(): string
	{
		if (
			$this->parent() instanceof Page ||
			$this->parent() instanceof User
		) {
			return $this->id ??= $this->parent()->id() . '/' . $this->filename();
		}

		return $this->id ??= $this->filename();
	}

	/**
	 * Compares the current object with the given file object
	 */
	public function is(File $file): bool
	{
		return $this->id() === $file->id();
	}

	/**
	 * Checks if the files is accessible.
	 * This permission depends on the `read` option until v5
	 */
	public function isAccessible(): bool
	{
		// TODO: remove this check when `read` option deprecated in v5
		if ($this->isReadable() === false) {
			return false;
		}

		static $accessible   = [];
		$role                = $this->kirby()->user()?->role()->id() ?? '__none__';
		$template            = $this->template() ?? '__none__';
		$accessible[$role] ??= [];

		return $accessible[$role][$template] ??= $this->permissions()->can('access');
	}

	/**
	 * Check if the file can be listable by the current user
	 * This permission depends on the `read` option until v5
	 */
	public function isListable(): bool
	{
		// TODO: remove this check when `read` option deprecated in v5
		if ($this->isReadable() === false) {
			return false;
		}

		// not accessible also means not listable
		if ($this->isAccessible() === false) {
			return false;
		}

		static $listable   = [];
		$role              = $this->kirby()->user()?->role()->id() ?? '__none__';
		$template          = $this->template() ?? '__none__';
		$listable[$role] ??= [];

		return $listable[$role][$template] ??= $this->permissions()->can('list');
	}

	/**
	 * Check if the file can be read by the current user
	 *
	 * @todo Deprecate `read` option in v5 and make the necessary changes for `access` and `list` options.
	 */
	public function isReadable(): bool
	{
		static $readable   = [];
		$role              = $this->kirby()->user()?->role()->id() ?? '__none__';
		$template          = $this->template() ?? '__none__';
		$readable[$role] ??= [];

		return $readable[$role][$template] ??= $this->permissions()->can('read');
	}

	/**
	 * Creates a unique media hash
	 * @internal
	 */
	public function mediaHash(): string
	{
		return $this->mediaToken() . '-' . $this->modifiedFile();
	}

	/**
	 * Returns the absolute path to the file in the public media folder
	 * @internal
	 */
	public function mediaRoot(): string
	{
		return $this->parent()->mediaRoot() . '/' . $this->mediaHash() . '/' . $this->filename();
	}

	/**
	 * Creates a non-guessable token string for this file
	 * @internal
	 */
	public function mediaToken(): string
	{
		$token = $this->kirby()->contentToken($this, $this->id());
		return substr($token, 0, 10);
	}

	/**
	 * Returns the absolute Url to the file in the public media folder
	 * @internal
	 */
	public function mediaUrl(): string
	{
		return $this->parent()->mediaUrl() . '/' . $this->mediaHash() . '/' . $this->filename();
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @param string|null $handler date, intl or strftime
	 */
	public function modified(
		string|IntlDateFormatter|null $format = null,
		string|null $handler = null,
		string|null $languageCode = null
	): string|int|false {
		$file     = $this->modifiedFile();
		$content  = $this->modifiedContent($languageCode);
		$modified = max($file, $content);

		return Str::date($modified, $format, $handler);
	}

	/**
	 * Timestamp of the last modification
	 * of the content file
	 */
	protected function modifiedContent(string|null $languageCode = null): int
	{
		return $this->storage()->modified('published', $languageCode) ?? 0;
	}

	/**
	 * Timestamp of the last modification
	 * of the source file
	 */
	protected function modifiedFile(): int
	{
		return F::modified($this->root());
	}

	/**
	 * Returns the parent Page object
	 */
	public function page(): Page|null
	{
		if ($this->parent() instanceof Page) {
			return $this->parent();
		}

		return null;
	}

	/**
	 * Returns the panel info object
	 */
	public function panel(): Panel
	{
		return new Panel($this);
	}

	/**
	 * Returns the parent object
	 */
	public function parent(): Page|Site|User
	{
		return $this->parent ??= $this->kirby()->site();
	}

	/**
	 * Returns the parent id if a parent exists
	 * @internal
	 */
	public function parentId(): string
	{
		return $this->parent()->id();
	}

	/**
	 * Returns a collection of all parent pages
	 */
	public function parents(): Pages
	{
		if ($this->parent() instanceof Page) {
			return $this->parent()->parents()->prepend(
				$this->parent()->id(),
				$this->parent()
			);
		}

		return new Pages();
	}

	/**
	 * Return the permanent URL to the file using its UUID
	 * @since 3.8.0
	 */
	public function permalink(): string|null
	{
		return $this->uuid()?->url();
	}

	/**
	 * Returns the permissions object for this file
	 */
	public function permissions(): FilePermissions
	{
		return new FilePermissions($this);
	}

	/**
	 * Returns the absolute root to the file
	 */
	public function root(): string|null
	{
		return $this->root ??= $this->parent()->root() . '/' . $this->filename();
	}

	/**
	 * Returns the FileRules class to
	 * validate any important action.
	 */
	protected function rules(): FileRules
	{
		return new FileRules();
	}

	/**
	 * Sets the Blueprint object
	 *
	 * @return $this
	 */
	protected function setBlueprint(array|null $blueprint = null): static
	{
		if ($blueprint !== null) {
			$blueprint['model'] = $this;
			$this->blueprint = new FileBlueprint($blueprint);
		}

		return $this;
	}

	/**
	 * Returns the parent Files collection
	 * @internal
	 */
	protected function siblingsCollection(): Files
	{
		return $this->parent()->files();
	}

	/**
	 * Returns the parent Site object
	 */
	public function site(): Site
	{
		if ($this->parent() instanceof Site) {
			return $this->parent();
		}

		return $this->kirby()->site();
	}

	/**
	 * Returns the final template
	 */
	public function template(): string|null
	{
		return $this->template ??= $this->content('default')->get('template')->value();
	}

	/**
	 * Returns siblings with the same template
	 */
	public function templateSiblings(bool $self = true): Files
	{
		return $this->siblings($self)->filter('template', $this->template());
	}

	/**
	 * Extended info for the array export
	 * by injecting the information from
	 * the asset.
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), $this->asset()->toArray(), [
			'id'       => $this->id(),
			'template' => $this->template(),
		]);
	}

	/**
	 * Returns the Url
	 */
	public function url(): string
	{
		return $this->url ??= ($this->kirby()->component('file::url'))($this->kirby(), $this);
	}

	/**
	 * Simplified File URL that uses the parent
	 * Page URL and the filename as a more stable
	 * alternative for the media URLs.
	 */
	public function previewUrl(): string|null
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
