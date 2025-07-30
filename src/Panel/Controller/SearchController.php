<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
abstract class SearchController extends Controller
{
	public function __construct(
		public string $query,
		public int $limit,
		public int $page
	) {
		parent::__construct();
	}

	public static function factory(): static
	{
		$kirby   = App::instance();
		$request = $kirby->request();
		$limit   = $kirby->option('panel.search.limit', 10);

		return new static(
			query: $request->get('query', ''),
			limit: (int)$request->get('limit', $limit),
			page:  (int)$request->get('page', 1)
		);
	}
}
