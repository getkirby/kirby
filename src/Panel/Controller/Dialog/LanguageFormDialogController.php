<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

/**
 * Dialog controller for creating a new language
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class LanguageFormDialogController extends DialogController
{
	public function __construct(
		public Language|null $language = null
	) {
		parent::__construct();
	}

	public static function factory(string|null $language = null): static
	{
		if ($language !== null) {
			$language = Find::language($language);
		}

		return new static($language);
	}

	public function fields(): array
	{
		return [
			'name' => [
				'counter'  => false,
				'label'    => I18n::translate('language.name'),
				'type'     => 'text',
				'required' => true,
				'icon'     => 'title'
			],
			'code' => [
				'label'    => I18n::translate('language.code'),
				'type'     => 'text',
				// the code of an existing language cannot be changed
				'disabled' => $this->language !== null,
				'required' => true,
				'counter'  => false,
				'icon'     => 'translate',
				'width'    => '1/2'
			],
			'direction' => [
				'label'    => I18n::translate('language.direction'),
				'type'     => 'select',
				'required' => true,
				'empty'    => false,
				'options'  => [
					[
						'value' => 'ltr',
						'text' => I18n::translate('language.direction.ltr')
					],
					[
						'value' => 'rtl',
						'text' => I18n::translate('language.direction.rtl')
					]
				],
				'width'    => '1/2'
			],
			// if the locale settings is more complex than just a
			// single string, the text field won't do it anymore.
			// Changes can only be made in the language file and
			// we display a warning box instead.
			'locale' => [
				'counter' => false,
				'label'   => I18n::translate('language.locale'),
				'text'    => I18n::translate('language.locale.warning'),
				'type'    => is_array($this->locale()) ? 'info' : 'text',
			],
		];
	}

	public function load(): Dialog
	{
		return new FormDialog(
			component: 'k-language-dialog',
			fields: $this->fields(),
			submitButton: I18n::translate(
				$this->language ? 'save' : 'language.create'
			),
			value: $this->value()
		);
	}

	public function locale(): array|string|null
	{
		$locale = $this->language?->locale();

		if ($locale === null) {
			return null;
		}

		// use the first locale key if there's only one
		if (count($locale) === 1) {
			$locale = A::first($locale);
		}

		return $locale;
	}

	public function submit(): array
	{
		$data = $this->request->get([
			'code',
			'direction',
			'locale',
			'name'
		]);

		if ($this->language === null) {
			$this->kirby->languages()->create($data);
		} else {
			$this->language = $this->language->update($data);
		}

		return [
			'event' => $this->language ? 'language.update' : 'language.create'
		];
	}

	public function value(): array
	{
		return [
			'code'      => $this->language?->code() ?? '',
			'direction' => $this->language?->direction() ?? 'ltr',
			'locale'    => $this->locale() ?? '',
			'name'      => $this->language?->name() ?? '',
			'rules'     => $this->language?->rules() ?? '',
		];
	}
}
