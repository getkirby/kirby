<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

return [
	'language' => [
		'pattern' => 'languages/(:any)',
		'when'    => function (): bool {
			return App::instance()->option('languages.variables', true) !== false;
		},
		'action'  => function (string $code) {
			$kirby        = App::instance();
			$language     = Find::language($code);
			$link         = '/languages/' . $language->code();
			$strings      = [];
			$foundation   = $kirby->defaultLanguage()->translations();
			$translations = $language->translations();

			// TODO: update following line and adapt for update and delete options
			// when new `languageVariables.*` permissions available
			$canUpdate = $kirby->user()?->role()->permissions()->for('languages', 'update') === true;

			ksort($foundation);

			foreach ($foundation as $key => $value) {
				$strings[] = [
					'key'     => $key,
					'value'   => $translations[$key] ?? null,
					'options' => [
						[
							'click'    => 'update',
							'disabled' => $canUpdate === false,
							'icon'     => 'edit',
							'text'     => I18n::translate('edit'),
						],
						[
							'click'    => 'delete',
							'disabled' => $canUpdate === false || $language->isDefault() === false,
							'icon'     => 'trash',
							'text'     => I18n::translate('delete'),
						]
					]
				];
			}

			$next = function () use ($language) {
				if ($next = $language->next()) {
					return [
						'link'  => '/languages/' . $next->code(),
						'title' => $next->name(),
					];
				}
			};

			$prev = function () use ($language) {
				if ($prev = $language->prev()) {
					return [
						'link'  => '/languages/' . $prev->code(),
						'title' => $prev->name(),
					];
				}
			};

			return [
				'component'  => 'k-language-view',
				'breadcrumb' => [
					[
						'label' => $name = $language->name(),
						'link'  => $link,
					]
				],
				'props'      => [
					'deletable'    => $language->isDeletable(),
					'code'         => Escape::html($language->code()),
					'default'      => $language->isDefault(),
					'direction'    => $language->direction(),
					'id'           => $language->code(),
					'info'         => [
						[
							'label' => 'Status',
							'value' => I18n::translate('language.' . ($language->isDefault() ? 'default' : 'secondary')),
						],
						[
							'label' => I18n::translate('language.code'),
							'value' => $language->code(),
						],
						[
							'label' => I18n::translate('language.locale'),
							'value' => $language->locale(LC_ALL)
						],
						[
							'label' => I18n::translate('language.direction'),
							'value' => I18n::translate('language.direction.' . $language->direction()),
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
					]),
					'variables' => $kirby->option('languages.variables', true)
				]
			];
		}
	]
];
