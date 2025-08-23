<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Language;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Item\LanguageItem;
use Kirby\Panel\Ui\View;
use Override;

/**
 * Controls the languages view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LanguagesViewController extends ViewController
{
	public function buttons(): ViewButtons
	{
		return ViewButtons::view('languages')->defaults('create');
	}

	public function languages(): array
	{
		return $this->kirby->languages()->values(
			fn (Language $language) => (new LanguageItem($language))->props()
		);
	}

	#[Override]
	public function load(): View
	{
		return new View(
			component: 'k-languages-view',
			buttons:   $this->buttons(),
			languages: $this->languages(),
			variables: $this->kirby->option('languages.variables', true)
		);
	}
}
