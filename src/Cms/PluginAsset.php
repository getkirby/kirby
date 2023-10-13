<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;

/**
 * Representing a plugin asset with methods
 * to manage the asset file between the plugin
 * and media folder
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PluginAsset
{
	public function __construct(
		protected string $path,
		protected string $root,
		protected Plugin $plugin
	) {
	}

	public function extension(): string
	{
		return F::extension($this->path());
	}

	public function filename(): string
	{
		return F::filename($this->path());
	}

	/**
	 * Create a unique media hash
	 */
	public function mediaHash(): string
	{
		return crc32($this->filename()) . '-' . $this->modified();
	}

	/**
	 * Absolute path to the asset file in the media folder
	 */
	public function mediaRoot(): string
	{
		return $this->plugin()->mediaRoot() . '/' . $this->mediaHash() . '/' . $this->path();
	}

	/**
	 * Public accessible url path for the asset
	 */
	public function mediaUrl(): string
	{
		return $this->plugin()->mediaUrl() . '/' . $this->mediaHash() . '/' . $this->path();
	}

	/**
	 * Timestamp when asset file was last modified
	 */
	public function modified(): int|false
	{
		return F::modified($this->root());
	}

	public function path(): string
	{
		return $this->path;
	}

	public function plugin(): Plugin
	{
		return $this->plugin;
	}

	/**
	 * Publishes the asset file to the plugin's media folder
	 * by creating a symlink
	 */
	public function publish(): void
	{
		F::link($this->root(), $this->mediaRoot(), 'symlink');
	}

	/**
	 * @internal
	 * @since 4.0.0
	 * @deprecated 4.0.0
	 * @codeCoverageIgnore
	 */
	public function publishAt(string $path): void
	{
		$media = $this->plugin()->mediaRoot() . '/' . $path;
		F::link($this->root(), $media, 'symlink');
	}

	public function root(): string
	{
		return $this->root;
	}

	/**
	 * @see ::mediaUrl
	 */
	public function url(): string
	{
		return $this->mediaUrl();
	}

	/**
	 * @see ::url
	 */
	public function __toString(): string
	{
		return $this->url();
	}
}
