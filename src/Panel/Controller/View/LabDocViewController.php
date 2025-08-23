<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Lab\Doc;
use Kirby\Panel\Lab\Docs;
use Kirby\Panel\Ui\Button\ViewButton;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use Kirby\Panel\Ui\View\ErrorView;
use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LabDocViewController extends ViewController
{
	protected Doc|null $doc;

	public function __construct(
		protected string $component
	) {
		$this->doc = Doc::factory($component);
	}

	public function breadcrumb(): array
	{
		return [
			[
				'label' => 'Docs',
				'link'  => 'lab/docs'
			],
			[
				'label' => $this->component,
				'link'  => 'lab/docs/' . $this->component
			]
		];
	}

	public function buttons(): ViewButtons
	{
		$buttons = [];

		if ($lab = $this->doc->lab()) {
			$buttons[] = new ViewButton(
				icon: 'lab',
				link: '/lab/' . $lab,
				text: 'Lab examples'
			);
		}

		$buttons[] = new ViewButton(
			icon:   'github',
			link:   $this->doc->source(),
			target: '_blank'
		);

		return new ViewButtons($buttons);
	}

	#[Override]
	public function load(): View
	{
		// @codeCoverageIgnoreStart
		if (Docs::isInstalled() === false) {
			return new ErrorView(
				access: 'inside',
				message: 'The UI docs are not installed.'
			);
		}

		if ($this->doc === null) {
			return new ErrorView(
				access: 'inside',
				message: 'No UI docs found for ' . $this->component . '.'
			);
		}
		// @codeCoverageIgnoreEnd

		return new View(
			component: 'k-lab-docs-view',
			breadcrumb: $this->breadcrumb(),
			buttons: $this->buttons(),
			docs: $this->doc->toArray(),
			lab: $this->doc->lab(),
			title: $this->component,
		);
	}
}
