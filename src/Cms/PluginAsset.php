<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;

class PluginAsset
{
	public function __construct(
		protected string $path,
		protected string $root,
		protected Plugin $plugin
	) {
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
		return $this->plugin()->mediaRoot() . '/' . $this->path();
	}

	/**
	 * Public accessible url path for the asset
	 */
	public function mediaUrl(): string
	{
		return $this->plugin()->mediaUrl() . '/' . $this->path() . '?m=' . $this->mediaHash();
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
	 *
	 * @codeCoverageIgnore
	 */
	public function publish(): void
	{
		F::link($this->root(), $this->mediaRoot(), 'symlink');
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
}
