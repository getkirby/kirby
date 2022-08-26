<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\Filesystem\F;
use Throwable;

/**
 * Simple PHP view engine
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class View
{
	/**
	 * The absolute path to the view file
	 */
	protected string $file;

	/**
	 * The view data
	 */
	protected array $data = [];

	/**
	 * Creates a new view object
	 */
	public function __construct(string $file, array $data = [])
	{
		$this->file = $file;
		$this->data = $data;
	}

	/**
	 * Returns the view's data array
	 * without globals.
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Checks if the template file exists
	 */
	public function exists(): bool
	{
		return is_file($this->file()) === true;
	}

	/**
	 * Returns the view file
	 */
	public function file(): string
	{
		return $this->file;
	}

	/**
	 * Creates an error message for the missing view exception
	 */
	protected function missingViewMessage(): string
	{
		return 'The view does not exist: ' . $this->file();
	}

	/**
	 * Renders the view
	 */
	public function render(): string
	{
		if ($this->exists() === false) {
			throw new Exception($this->missingViewMessage());
		}

		ob_start();

		$exception = null;
		try {
			F::load($this->file(), null, $this->data());
		} catch (Throwable $e) {
			$exception = $e;
		}

		$content = ob_get_contents();
		ob_end_clean();

		if ($exception === null) {
			return $content;
		}

		throw $exception;
	}

	/**
	 * Alias for View::render()
	 */
	public function toString(): string
	{
		return $this->render();
	}

	/**
	 * Magic string converter to enable
	 * converting view objects to string
	 */
	public function __toString(): string
	{
		return $this->render();
	}
}
