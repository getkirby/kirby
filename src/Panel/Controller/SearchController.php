<?php

namespace Kirby\Panel\Controller;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 *
 * @codeCoverageIgnore
 */
abstract class SearchController extends Controller
{
	protected string $query;
	protected int $limit;
	protected int $page;

	public function __construct()
	{
		parent::__construct();

		$this->query = $this->request->get('query', '');
		$this->limit = (int)$this->request->get('limit', $this->kirby->option('panel.search.limit', 10));
		$this->page  = (int)$this->request->get('page', 1);
	}

	abstract public function results(): array;
}
