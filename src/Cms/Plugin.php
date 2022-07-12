<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

/**
 * Represents a Plugin and handles parsing of
 * the composer.json. It also creates the prefix
 * and media url for the plugin.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Plugin extends Model
{
	protected $extends;
	protected $info;
	protected $name;
	protected $root;

	/**
	 * @param string $key
	 * @param array|null $arguments
	 * @return mixed|null
	 */
	public function __call(string $key, array $arguments = null)
	{
		return $this->info()[$key] ?? null;
	}

	/**
	 * Plugin constructor
	 *
	 * @param string $name
	 * @param array $extends
	 */
	public function __construct(string $name, array $extends = [])
	{
		$this->setName($name);
		$this->extends = $extends;
		$this->root    = $extends['root'] ?? dirname(debug_backtrace()[0]['file']);
		$this->info    = empty($extends['info']) === false && is_array($extends['info']) ? $extends['info'] : null;

		unset($this->extends['root'], $this->extends['info']);
	}

	/**
	 * Returns the array with author information
	 * from the composer file
	 *
	 * @return array
	 */
	public function authors(): array
	{
		return $this->info()['authors'] ?? [];
	}

	/**
	 * Returns a comma-separated list with all author names
	 *
	 * @return string
	 */
	public function authorsNames(): string
	{
		$names = [];

		foreach ($this->authors() as $author) {
			$names[] = $author['name'] ?? null;
		}

		return implode(', ', array_filter($names));
	}

	/**
	 * @return array
	 */
	public function extends(): array
	{
		return $this->extends;
	}

	/**
	 * Returns the unique id for the plugin
	 *
	 * @return string
	 */
	public function id(): string
	{
		return $this->name();
	}

	/**
	 * @return array
	 */
	public function info(): array
	{
		if (is_array($this->info) === true) {
			return $this->info;
		}

		try {
			$info = Data::read($this->manifest());
		} catch (Exception $e) {
			// there is no manifest file or it is invalid
			$info = [];
		}

		return $this->info = $info;
	}

	/**
	 * Returns the link to the plugin homepage
	 *
	 * @return string|null
	 */
	public function link(): ?string
	{
		$info     = $this->info();
		$homepage = $info['homepage'] ?? null;
		$docs     = $info['support']['docs'] ?? null;
		$source   = $info['support']['source'] ?? null;

		$link = $homepage ?? $docs ?? $source;

		return V::url($link) ? $link : null;
	}

	/**
	 * @return string
	 */
	public function manifest(): string
	{
		return $this->root() . '/composer.json';
	}

	/**
	 * @return string
	 */
	public function mediaRoot(): string
	{
		return App::instance()->root('media') . '/plugins/' . $this->name();
	}

	/**
	 * @return string
	 */
	public function mediaUrl(): string
	{
		return App::instance()->url('media') . '/plugins/' . $this->name();
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function option(string $key)
	{
		return $this->kirby()->option($this->prefix() . '.' . $key);
	}

	/**
	 * @return string
	 */
	public function prefix(): string
	{
		return str_replace('/', '.', $this->name());
	}

	/**
	 * @return string
	 */
	public function root(): string
	{
		return $this->root;
	}

	/**
	 * @param string $name
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function setName(string $name)
	{
		if (preg_match('!^[a-z0-9-]+\/[a-z0-9-]+$!i', $name) !== 1) {
			throw new InvalidArgumentException('The plugin name must follow the format "a-z0-9-/a-z0-9-"');
		}

		$this->name = $name;
		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'authors'     => $this->authors(),
			'description' => $this->description(),
			'name'        => $this->name(),
			'license'     => $this->license(),
			'link'        => $this->link(),
			'root'        => $this->root(),
			'version'     => $this->version()
		];
	}
}
