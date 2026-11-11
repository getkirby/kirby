<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
abstract class SearchController extends Controller
{
	public function __construct(
		public string $query = '',
		public int|null $limit = null,
		public int $page = 1
	) {
		parent::__construct();
	}

	public static function factory(): static
	{
		$kirby   = App::instance();
		$request = $kirby->request();
		$limit   = $kirby->option('panel.search.limit', 10);

		return new static(
			query: $request->string('query', ''),
			limit: $request->int('limit', $limit),
			page:  $request->int('page', 1)
		);
	}
}
