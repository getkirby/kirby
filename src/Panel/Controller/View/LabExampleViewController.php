<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Doc;
use Kirby\Panel\Lab\Example;
use Kirby\Panel\Ui\Button\ViewButton;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LabExampleViewController extends ViewController
{
	public function __construct(
		public Category $category,
		public Example $example
	) {
		parent::__construct();
	}

	public function breadcrumb(): array
	{
		return [
			[
				'label' => $this->category->name(),
			],
			[
				'label' => $this->example->title(),
				'link'  => $this->example->url()
			]
		];
	}

	public function buttons(): ViewButtons
	{
		$buttons = [];

		if ($docs = $this->doc()?->name) {
			$buttons[] = new ViewButton(
				icon: 'book',
				drawer: 'lab/docs/' . $docs,
				text: $docs,
			);
		}

		if ($github = $this->github()) {
			$buttons[] = new ViewButton(
				icon:   'github',
				link:   $github,
				target: '_blank'
			);
		}

		return new ViewButtons($buttons);
	}

	protected function doc(): Doc|null
	{
		if ($doc = $this->example->props()['docs'] ?? null) {
			return Doc::factory($doc);
		}

		// @codeCoverageIgnoreStart
		return null;
		// @codeCoverageIgnoreEnd
	}

	public static function factory(
		string $category,
		string $id,
		string|null $tab = null
	) {
		$category = Category::factory($category);
		$example  = $category->example($id, $tab);
		return new static($category, $example);
	}

	public function github(): string|null
	{
		$github = $this->doc()?->source();

		if ($source = $this->example->props()['source'] ?? null) {
			$github ??= 'https://github.com/getkirby/kirby/tree/main/' . $source;
		}

		return $github;
	}

	public function load(): View
	{
		return new View(
			component: 'k-lab-playground-view',
			breadcrumb: $this->breadcrumb(),
			buttons: $this->buttons(),
			docs: $this->doc()?->name,
			examples: $this->example->vue()['examples'],
			file: $this->example->module(),
			github: $this->github(),
			props: $this->example->props(),
			styles: $this->example->vue()['style'],
			tab: $this->example->tab(),
			tabs: array_values($this->example->tabs()),
			template: $this->example->vue()['template'],
			title: $this->example->title()
		);
	}
}
