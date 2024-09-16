<?php

namespace Kirby\Blueprint;

use Closure;
use Kirby\Cms\App;
use Kirby\Data\Yaml;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;

/**
 * Config
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage once blueprint refactoring is done
 * @codeCoverageIgnore
 */
class Config
{
	public string $file;
	public string $id;
	public string|array|Closure|null $plugin;
	public string $root;

	public function __construct(
		public string $path
	) {
		$kirby = App::instance();

		$this->id     = basename($this->path);
		$this->root   = $kirby->root('blueprints');
		$this->file   = $this->root . '/' . $this->path . '.yml';
		$this->plugin = $kirby->extension('blueprints', $this->path);
	}

	public function read(): array
	{
		if (F::exists($this->file, $this->root) === true) {
			return $this->unpack($this->file);
		}

		return $this->unpack($this->plugin);
	}

	public function write(array $props): bool
	{
		return Yaml::write($this->file, $props);
	}

	public function unpack(string|array|Closure|null $extension): array
	{
		return match (true) {
			// extension does not exist
			is_null($extension)
				=> throw new NotFoundException(
					message: '"' . $this->path . '" could not be found'
				),

			// extension is stored as a file path
			is_string($extension)
				=> Yaml::read($extension),

			// extension is a callback to be resolved
			is_callable($extension)
				=> $extension(App::instance()),

			// extension is already defined as array
			default
			=> $extension
		};
	}
}
