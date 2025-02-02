<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Language;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguageUpdateDialog extends LanguageCreateDialog
{
	use IsForLanguage;

	public function __construct(
		public Language $language
	) {
		parent::__construct(
			submitButton: I18n::translate('save')
		);
	}

	public function fields(): array
	{
		$fields = parent::fields();

		// the code of an existing language cannot be changed
		$fields['code']['disabled'] = true;

		// if the locale settings is more complex than just a
		// single string, the text field won't do it anymore.
		// Changes can only be made in the language file and
		// we display a warning box instead
		if (is_array($this->locale()) === true) {
			$fields['locale'] = [
				'label' => $fields['locale']['label'],
				'type'  => 'info',
				'text'  => I18n::translate('language.locale.warning')
			];
		}

		return $fields;
	}

	public function locale(): string|array
	{
		$locale = $this->language->locale();

		// use the first locale key if there's only one
		if (count($locale) === 1) {
			$locale = A::first($locale);
		}

		return $locale;
	}

	public function submit(): array
	{
		$data = $this->request->get(['direction', 'locale', 'name']);
		$this->language->update($data);

		return [
			'event' => 'language.update'
		];
	}

	public function value(): array
	{
		return [
			'code'      => $this->language->code(),
			'direction' => $this->language->direction(),
			'locale'    => $this->locale(),
			'name'      => $this->language->name(),
			'rules'     => $this->language->rules(),
		];
	}
}
