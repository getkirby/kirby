<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Language;
use Kirby\Cms\LanguageVariable;
use Kirby\Toolkit\I18n;

/**
 * Dialog to create a new language variable
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguageTranslationCreateDialog extends FormDialog
{
	use IsForLanguage;

	public function __construct(
		public Language $language,
		string|array|bool|null $cancelButton = null,
		string|array|bool|null $submitButton = null,
		public string|null $text = null,
	) {
		parent::__construct(
			fields: $this->fields(),
			cancelButton: $cancelButton,
			size: 'large',
			submitButton: $submitButton,
			value: $this->value(),
		);
	}

	public function fields(): array
	{
		return [
			'key' => [
				'counter' => false,
				'icon'    => null,
				'label'   => I18n::translate('language.variable.key'),
				'type'    => 'text'
			],
			'value' => [
				'buttons' => false,
				'counter' => false,
				'label'   => I18n::translate('language.variable.value'),
				'type'    => 'textarea'
			]
		];
	}

	public function submit(): true
	{
		$key   = $this->request->get('key', '');
		$value = $this->request->get('value', '');

		LanguageVariable::create($key, $value);

		if ($this->language->isDefault() === false) {
			$this->language->variable($key)->update($value);
		}

		return true;
	}

	public function value(): array
	{
		return [];
	}
}
