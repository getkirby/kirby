<?php


/**
 * Site Routes
 */
return [
	// @codeCoverageIgnoreStart
	[
		'pattern' => 'site',
		'action'  => function () {
			return $this->site();
		}
	],
	[
		'pattern' => 'site',
		'method'  => 'PATCH',
		'action'  => function () {
			return $this->site()->update($this->request()->body()->toArray(), $this->language(), true);
		}
	],
	[
		'pattern' => 'site/children',
		'method'  => 'GET',
		'action'  => function () {
			return $this->pages(null, $this->request()->query()->string('status'));
		}
	],
	[
		'pattern' => 'site/children',
		'method'  => 'POST',
		'action'  => function () {
			return $this->site()->createChild($this->request()->body()->toArray());
		}
	],
	[
		'pattern' => 'site/children/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			return $this->searchPages();
		}
	],
	[
		'pattern' => 'site/blueprint',
		'method'  => 'GET',
		'action'  => function () {
			return $this->site()->blueprint();
		}
	],
	[
		'pattern' => 'site/blueprints',
		'method'  => 'GET',
		'action'  => function () {
			return $this->site()->blueprints($this->request()->query()->string('field'));
		}
	],
	[
		'pattern' => 'site/find',
		'method'  => 'POST',
		'action'  => function () {
			return $this->site()->find(false, ...$this->request()->body()->toArray())->filter('isAccessible', true);
		}
	],
	[
		'pattern' => 'site/title',
		'method'  => 'PATCH',
		'action'  => function () {
			return $this->site()->changeTitle($this->request()->body()->string('title', ''));
		}
	],
	[
		'pattern' => 'site/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			$pages = $this
				->site()
				->index(true)
				->filter('isListable', true);

			if ($this->request()->method() === 'GET') {
				return $pages->search($this->request()->query()->string('q'));
			}

			return $pages->query(array_filter([
				'limit'    => $this->request()->body()->int('limit'),
				'offset'   => $this->request()->body()->int('offset'),
				'paginate' => $this->request()->body()->get('paginate'),
				'search'   => $this->request()->body()->get('search'),
			], fn ($value) => $value !== null));
		}
	],
	[
		'pattern' => 'site/fields/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $fieldName, string|null $path = null) {
			return $this->fieldApi($this->site(), $fieldName, $path);
		}
	],
	// @codeCoverageIgnoreEnd
];
