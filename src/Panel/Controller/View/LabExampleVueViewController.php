<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Http\Response;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Example;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LabExampleVueViewController extends ViewController
{
	public function __construct(
		public Example $example
	) {
		parent::__construct();
	}

	public static function factory(
		string $category,
		string $id,
		string|null $tab = null
	) {
		$example = Category::factory($category)->example($id, $tab);
		return new static($example);
	}

	public function load(): Response
	{
		return $this->example->serve();
	}
}
