<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;

/**
 * Controls the search view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SearchViewController extends ViewController
{
	public function load(): View
	{
		return new View(
			component: 'k-search-view',
			type: $this->request->get('type')
		);
	}
}
