<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\Escape;

return [
	'language' => [
		'pattern' => 'languages/(:any)',
		'action'  => function (string $code) {
			$language     = Find::language($code);
			$link         = '/languages/' . $language->code();
			$strings      = [];
			$foundation   = App::instance()->defaultLanguage()->translations();
			$translations = $language->translations();

			ksort($foundation);

			foreach ($foundation as $key => $value) {
				$strings[] = [
					'key'   => $key,
					'value' => $translations[$key] ?? null,
					'options' => [
						[
							'click' => 'update',
							'icon'  => 'edit',
							'text'  => t('edit'),
						],
						[
							'click' => 'delete',
							'icon'  => 'trash',
							'text'  => t('delete'),
						]
					]
				];
			}

			return [
				'component' => 'k-language-view',
				'breadcrumb' => [
					[
						'label' => $name = Escape::html($language->name()),
						'link'  => $link,
					]
				],
				'props'     => [
					'code'         => Escape::html($language->code()),
					'default'      => $language->isDefault(),
					'direction'    => $language->direction(),
					'id'           => $language->code(),
					'name'         => $name,
					'translations' => $strings
				]
			];
		}
	],
	'languages' => [
		'pattern' => 'languages',
		'action'  => function () {
			$kirby = App::instance();

			return [
				'component' => 'k-languages-view',
				'props'     => [
					'languages' => $kirby->languages()->values(fn ($language) => [
						'default' => $language->isDefault(),
						'id'      => $language->code(),
						'info'    => Escape::html($language->code()),
						'text'    => Escape::html($language->name()),
					])
				]
			];
		}
	],
];
