<?php

namespace Kirby\Panel\Ui\Drawers;

use Kirby\Panel\Lab\Doc;
use Kirby\Panel\Lab\Docs;
use Kirby\Panel\Ui\Drawer;
use Kirby\Panel\Ui\Renderable;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 *
 * @codeCoverageIgnore
 */
class LabsDocsDrawer extends Renderable
{
	public function __construct(
		public string $component
	) {
	}

	public static function for(string $component): static
	{
		return new static($component);
	}

	public function render(): array
	{
		if (Docs::isInstalled() === false) {
			$drawer = new TextDrawer(
				text: 'The UI docs are not installed.'
			);
			return $drawer->render();
		}

		$doc    = Doc::factory($this->component);
		$drawer = new Drawer(
			component: 'k-lab-docs-drawer',
			icon:      'book',
			title:     $this->component,
			docs:      $doc->toArray()
		);

		return $drawer->render();
	}
}
