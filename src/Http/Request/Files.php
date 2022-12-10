<?php

namespace Kirby\Http\Request;

/**
 * The Files object sanitizes
 * the input coming from the $_FILES
 * global. Especially for multiple uploads
 * for the same key, it will produce a more
 * usable array.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Files
{
	use Data;

	/**
	 * Sanitized array of all received files
	 */
	protected array $files = [];

	/**
	 * Creates a new Files object
	 * Pass your own array to mock
	 * uploads.
	 */
	public function __construct(array|null $files = null)
	{
		$files ??= $_FILES;

		foreach ($files as $key => $file) {
			if (is_array($file['name'])) {
				foreach ($file['name'] as $i => $name) {
					$this->files[$key][] = [
						'name'     => $file['name'][$i]      ?? null,
						'type'     => $file['type'][$i]      ?? null,
						'tmp_name' => $file['tmp_name'][$i]  ?? null,
						'error'    => $file['error'][$i]     ?? null,
						'size'     => $file['size'][$i]      ?? null,
					];
				}
			} else {
				$this->files[$key] = $file;
			}
		}
	}

	/**
	 * The data method returns the files
	 * array. This is only needed to make
	 * the Data trait work for the Files::get($key)
	 * method.
	 */
	public function data(): array
	{
		return $this->files;
	}
}
