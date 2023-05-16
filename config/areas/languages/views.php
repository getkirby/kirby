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

			$next = function () use ($language) {
				if ($next = $language->next()) {
					return [
						'link'    => '/languages/' . $next->code(),
						'tooltip' => $next->name(),
					];
				}
			};

			$prev = function () use ($language) {
				if ($prev = $language->prev()) {
					return [
						'link'    => '/languages/' . $prev->code(),
						'tooltip' => $prev->name(),
					];
				}
			};

			return [
				'component' => 'k-language-view',
				'breadcrumb' => [
					[
						'label' => $name = Escape::html($language->name()),
						'link'  => $link,
					]
				],
				'props'     => [
					'deletable'    => $language->isDeletable(),
					'code'         => Escape::html($language->code()),
					'default'      => $language->isDefault(),
					'direction'    => $language->direction(),
					'id'           => $language->code(),
					'info'         => [
						[
							'label'  => 'Status',
							'value'  => $language->isDefault() ? 'Default language' : 'Secondary language',
						],
						[
							'label' => t('language.code'),
							'value' => $language->code(),
						],
						[
							'label' => t('language.locale'),
							'value' => $language->locale(LC_ALL)
						],
						[
							'label' => t('language.direction'),
							'value' => t('language.direction.'. $language->direction()),
						],
					],
					'name'         => $name,
					'next'         => $next,
					'prev'         => $prev,
					'translations' => $strings,
					'url'          => $language->url(),
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
						'deletable' => $language->isDeletable(),
						'default'   => $language->isDefault(),
						'id'        => $language->code(),
						'info'      => Escape::html($language->code()),
						'text'      => Escape::html($language->name()),
					])
				]
			];
		}
	],
];
