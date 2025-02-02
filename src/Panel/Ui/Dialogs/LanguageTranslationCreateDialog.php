<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Cms\LanguageVariable;
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
class LanguageTranslationCreateDialog extends FormDialog
{
	use IsForLanguageVariable;

	public function __construct(
		public Language $language,
		string|array|false|null $cancelButton = null,
		string|array|false|null $submitButton = null,
		public string|null $text = null,
	) {
		parent::__construct(
			fields:       $this->fields(),
			cancelButton: $cancelButton,
			size:         'large',
			submitButton: $submitButton,
			value:        $this->value(),
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
			'multiple' => [
				'label'   => I18n::translate('language.variable.multiple'),
				'text'    => I18n::translate('language.variable.multiple.text'),
				'help'    => I18n::translate('language.variable.multiple.help'),
				'type'    => 'toggle'
			],
			'value' => [
				'buttons' => false,
				'counter' => false,
				'label'   => I18n::translate('language.variable.value'),
				'type'    => 'textarea',
				'when'    => [
					'multiple' => false
				]
			],
			'entries' => [
				'field' => ['type' => 'text'],
				'label' => I18n::translate('language.variable.entries'),
				'help'  => I18n::translate('language.variable.entries.help'),
				'type'  => 'entries',
				'min'   => 1,
				'when'  => [
					'multiple' => true
				]
			]
		];
	}

	public function submit(): true
	{
		$key      = $this->request->get('key', '');
		$multiple = $this->request->get('multiple', false);

		$value = match ($multiple) {
			true    => $this->request->get('entries', []),
			default => $this->request->get('value', '')
		};

		LanguageVariable::create($key, $value);

		if ($this->language->isDefault() === false) {
			$this->language->variable($key)->update($value);
		}

		return true;
	}

	public function value(): array
	{
		return [
			'multiple' => false,
		];
	}
}
