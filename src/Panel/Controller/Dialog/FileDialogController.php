<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\DialogController;

/**
 * Controls a Panel dialog for a specific file
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
abstract class FileDialogController extends DialogController
{
	public function __construct(
		public File $file
	) {
		parent::__construct();
	}

	public static function factory(string $parent, string $id): static
	{
		return new static(file: Find::file($parent, $id));
	}
}
