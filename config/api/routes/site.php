<?php


/**
 * Site Routes
 */
return [

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
			return $this->site()->update($this->requestBody(), $this->language(), true);
		}
	],
	[
		'pattern' => 'site/children',
		'method'  => 'GET',
		'action'  => function () {
			return $this->pages(null, $this->requestQuery('status'));
		}
	],
	[
		'pattern' => 'site/children',
		'method'  => 'POST',
		'action'  => function () {
			return $this->site()->createChild($this->requestBody());
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
			return $this->site()->blueprints($this->requestQuery('section'));
		}
	],
	[
		'pattern' => 'site/find',
		'method'  => 'POST',
		'action'  => function () {
			return $this->site()->find(false, ...$this->requestBody());
		}
	],
	[
		'pattern' => 'site/title',
		'method'  => 'PATCH',
		'action'  => function () {
			return $this->site()->changeTitle($this->requestBody('title'));
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

			if ($this->requestMethod() === 'GET') {
				return $pages->search($this->requestQuery('q'));
			}

			return $pages->query($this->requestBody());
		}
	],
	[
		'pattern' => 'site/sections/(:any)',
		'method'  => 'GET',
		'action'  => function (string $sectionName) {
			return $this->site()->blueprint()->section($sectionName)?->toResponse();
		}
	],
	[
		'pattern' => 'site/fields/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $fieldName, string $path = null) {
			return $this->fieldApi($this->site(), $fieldName, $path);
		}
	]

];
