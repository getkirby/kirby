<?php


/**
 * Page Routes
 */
return [

	[
		'pattern' => 'pages/(:any)',
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->page($id);
		}
	],
	[
		'pattern' => 'pages/(:any)',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->update($this->requestBody(), $this->language(), true);
		}
	],
	[
		'pattern' => 'pages/(:any)',
		'method'  => 'DELETE',
		'action'  => function (string $id) {
			return $this->page($id)->delete($this->requestBody('force', false));
		}
	],
	[
		'pattern' => 'pages/(:any)/blueprint',
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->page($id)->blueprint();
		}
	],
	[
		'pattern' => 'pages/(:any)/blueprints',
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->page($id)->blueprints($this->requestQuery('section'));
		}
	],
	[
		'pattern' => 'pages/(:any)/children',
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->pages($id, $this->requestQuery('status'));
		}
	],
	[
		'pattern' => 'pages/(:any)/children',
		'method'  => 'POST',
		'action'  => function (string $id) {
			return $this->page($id)->createChild($this->requestBody());
		}
	],
	[
		'pattern' => 'pages/(:any)/children/search',
		'method'  => 'GET|POST',
		'action'  => function (string $id) {
			return $this->searchPages($id);
		}
	],
	[
		'pattern' => 'pages/(:any)/duplicate',
		'method'  => 'POST',
		'action'  => function (string $id) {
			return $this->page($id)->duplicate($this->requestBody('slug'), [
				'children' => $this->requestBody('children'),
				'files'    => $this->requestBody('files'),
			]);
		}
	],
	[
		'pattern' => 'pages/(:any)/slug',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeSlug($this->requestBody('slug'));
		}
	],
	[
		'pattern' => 'pages/(:any)/status',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeStatus($this->requestBody('status'), $this->requestBody('position'));
		}
	],
	[
		'pattern' => 'pages/(:any)/template',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeTemplate($this->requestBody('template'));
		}
	],
	[
		'pattern' => 'pages/(:any)/title',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeTitle($this->requestBody('title'));
		}
	],
	[
		'pattern' => 'pages/(:any)/sections/(:any)',
		'method'  => 'GET',
		'action'  => function (string $id, string $sectionName) {
			return $this->page($id)->blueprint()->section($sectionName)?->toResponse();
		}
	],
	[
		'pattern' => 'pages/(:any)/fields/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $id, string $fieldName, string $path = null) {
			if ($page = $this->page($id)) {
				return $this->fieldApi($page, $fieldName, $path);
			}
		}
	],
];
