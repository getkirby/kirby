<?php

namespace Kirby\Email;

use Kirby\Cms\File;
use Kirby\Cms\Files;

/**
 * An email attachment
 *
 * @package   Kirby Email
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.0.0
 */
class Attachment
{
	public function __construct(
		protected string $root
	) {
	}

	/**
	 * Creates an array fof attachment objects
	 */
	public static function factory(
		Files|array|File|string $files
	): static|array {
		if (is_iterable($files) === false) {
			return match (true) {
				$files instanceof File => new static(root: $files->root()),
				default                => new static(root: $files)
			};
		}

		$attachments = [];

		foreach ($files as $file) {
			$attachments[] = static::factory($file);
		}

		return $attachments;
	}

	/**
	 * Returns the absolute path to the attachment
	 */
	public function root(): string
	{
		return $this->root;
	}
}
