<?php

use Kirby\Form\Form;

/**
 * Page Routes
 */
return [
	// @codeCoverageIgnoreStart
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
			return $this->page($id)->update($this->request()->body()->toArray(), $this->language(), true);
		}
	],
	[
		'pattern' => 'pages/(:any)',
		'method'  => 'DELETE',
		'action'  => function (string $id) {
			return $this->page($id)->delete($this->request()->body()->bool('force', false));
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
			return $this->page($id)->blueprints($this->request()->query()->string('field'));
		}
	],
	[
		'pattern' => 'pages/(:any)/children',
		'method'  => 'GET',
		'action'  => function (string $id) {
			return $this->pages($id, $this->request()->query()->string('status'));
		}
	],
	[
		'pattern' => 'pages/(:any)/children',
		'method'  => 'POST',
		'action'  => function (string $id) {
			return $this->page($id)->createChild($this->request()->body()->toArray());
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
			return $this->page($id)->duplicate($this->request()->body()->string('slug'), [
				'children' => $this->request()->body()->bool('children', false),
				'files'    => $this->request()->body()->bool('files', false),
			]);
		}
	],
	[
		'pattern' => 'pages/(:any)/slug',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeSlug($this->request()->body()->string('slug', ''));
		}
	],
	[
		'pattern' => 'pages/(:any)/status',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeStatus($this->request()->body()->string('status', ''), $this->request()->body()->int('position'));
		}
	],
	[
		'pattern' => 'pages/(:any)/template',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeTemplate($this->request()->body()->string('template', ''));
		}
	],
	[
		'pattern' => 'pages/(:any)/title',
		'method'  => 'PATCH',
		'action'  => function (string $id) {
			return $this->page($id)->changeTitle($this->request()->body()->string('title', ''));
		}
	],
	[
		'pattern' => 'pages/(:any)/fields/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $id, string $fieldName, string|null $path = null) {
			if ($page = $this->page($id)) {
				$field = Form::for($page)->field($fieldName);
				return $this->forward($field, $path);
			}
		}
	],
	// @codeCoverageIgnoreEnd
];
