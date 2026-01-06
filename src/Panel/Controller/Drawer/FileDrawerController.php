<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\File;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Controller\View\FileViewController;
use Kirby\Panel\Ui\Drawer;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FileDrawerController extends DrawerController
{
	public function __construct(
		protected File $file
	) {
		parent::__construct();
	}

	public static function factory(string $parent, string $filename): static
	{
		return new static(file: Find::file($parent, $filename));
	}

	public function load(): Drawer
	{
		$view = (new FileViewController($this->file))->props();
		$props = [
			...$view,
			'component' => 'k-file-drawer',
			'icon'      => $this->file->panel()->image()['icon'],
			'options' => [
				[
					'icon'    => 'cog',
					'options' => $view['link'],
				],
				[
					'icon' => 'expand',
					'link' => $view['link']
				],
			]
		];

		unset($props['buttons']);

		return new Drawer(...$props);
	}
}
