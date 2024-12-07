<?php

use Kirby\Cms\App;
use Kirby\Panel\Controller\PageTree;

return [
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => function () {
			return (new PageTree())->children(
				parent: App::instance()->request()->get('parent'),
				moving: App::instance()->request()->get('move')
			);
		}
	],
	'tree.parents' => [
		'pattern' => 'site/tree/parents',
		'action'  => function () {
			return (new PageTree())->parents(
				page: App::instance()->request()->get('page'),
				includeSite: App::instance()->request()->get('root') === 'true',
			);
		}
	]
];
