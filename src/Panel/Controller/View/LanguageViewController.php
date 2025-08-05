<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Stat;
use Kirby\Panel\Ui\Stats;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\Escape;

/**
 * Controls the language view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LanguageViewController extends ViewController
{
	public function __construct(
		public Language $language
	) {
		parent::__construct();
	}

	public function breadcrumb(): array
	{
		return [
			[
				'label' => $this->language->name(),
				'link'  => '/languages/' . $this->language->code(),
			]
		];
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view('language', model: $this->language)->defaults(
			'open',
			'settings',
			'delete'
		);
	}

	public static function factory(string $code): static
	{
		return new static(language: Find::language($code));
	}

	public function info(): Stats
	{
		return new Stats(reports: [
			new Stat(
				label: 'Status',
				value: $this->i18n('language.' . ($this->language->isDefault() ? 'default' : 'secondary')),
			),
			new Stat(
				label: $this->i18n('language.code'),
				value: $this->language->code(),
			),
			new Stat(
				label: $this->i18n('language.locale'),
				value: $this->language->locale(LC_ALL),
			),
			new Stat(
				label: $this->i18n('language.direction'),
				value: $this->i18n('language.direction.' . $this->language->direction()),
			),
		]);
	}

	public function load(): View
	{
		return new View(
			component:    'k-language-view',
			buttons:      $this->buttons(),
			breadcrumb:   $this->breadcrumb(),
			code:         Escape::html($this->language->code()),
			deletable:    $this->language->isDeletable(),
			direction:    $this->language->direction(),
			id:           $this->language->code(),
			info:         $this->info()->reports(...),
			name:         $this->language->name(),
			next:         $this->next(),
			prev:         $this->prev(),
			translations: $this->translations(),
			url:          $this->language->url(),
		);
	}

	public function next(): array|null
	{
		if ($next = $this->language->next()) {
			return [
				'link'  => '/languages/' . $next->code(),
				'title' => $next->name(),
			];
		}

		return null;
	}

	public function prev(): array|null
	{
		if ($prev = $this->language->prev()) {
			return [
				'link'  => '/languages/' . $prev->code(),
				'title' => $prev->name(),
			];
		}

		return null;
	}

	public function translations(): array
	{
		$strings      = [];
		$foundation   = $this->kirby->defaultLanguage()?->translations() ?? [];
		$translations = $this->language->translations();

		// TODO: update following line and adapt for update and
		// delete options when `languageVariables.*` permissions available
		$canUpdate = $this->kirby->role()?->permissions()->for('languages', 'update') === true;

		ksort($foundation);

		foreach (array_keys($foundation) as $key) {
			$strings[] = [
				'key'     => $key,
				'value'   => $translations[$key] ?? null,
				'options' => [
					[
						'click'    => 'update',
						'disabled' => $canUpdate === false,
						'icon'     => 'edit',
						'text'     => $this->i18n('edit'),
					],
					[
						'click'    => 'delete',
						'disabled' => $canUpdate === false || $this->language->isDefault() === false,
						'icon'     => 'trash',
						'text'     => $this->i18n('delete'),
					]
				]
			];
		}

		return $strings;
	}
}
