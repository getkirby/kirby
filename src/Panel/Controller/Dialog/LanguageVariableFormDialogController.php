<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Dialog controller for creating a new language variable
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class LanguageVariableFormDialogController extends DialogController
{
	public function __construct(
		public Language $language,
		public LanguageVariable|null $variable = null
	) {
		parent::__construct();
	}

	public static function factory(
		string $language,
		string|null $key = null
	): static {
		$language = Find::language($language);

		if ($key !== null) {
			$key = $language->variable($key, decode: true);

			if ($key->exists() === false) {
				throw new NotFoundException(key: 'language.variable.notFound');
			}
		}

		return new static($language, $key);
	}

	public function fields(): array
	{
		return [
			'key' => [
				'counter'  => false,
				'icon'     => null,
				'label'    => $this->i18n('language.variable.key'),
				'type'     => 'text',
				// the key field cannot be changed
				'disabled' => $this->variable !== null
			],
			'multiple' => [
				'label'   => $this->i18n('language.variable.multiple'),
				'text'    => $this->i18n('language.variable.multiple.text'),
				'help'    => $this->i18n('language.variable.multiple.help'),
				'type'    => $this->variable ? 'hidden' : 'toggle'
			],
			'value' => [
				'buttons'   => false,
				'counter'   => false,
				'label'     => $this->i18n('language.variable.value'),
				'type'      => 'textarea',
				'when'      => ['multiple' => false],
				'autofocus' => $this->hasMultipleValues() === false
			],
			'entries' => [
				'field'     => ['type' => 'text'],
				'label'     => $this->i18n('language.variable.entries'),
				'help'      => $this->i18n('language.variable.entries.help'),
				'type'      => 'entries',
				'min'       => 1,
				'when'      => ['multiple' => true],
				'autofocus' => $this->hasMultipleValues() === true
			]
		];
	}

	/**
	 * Check if the variable has multiple values;
	 * ensure to use the default language for this check because
	 * the variable might not exist in the current language but
	 * already be defined in the default language with multiple values
	 */
	public function hasMultipleValues(): bool
	{
		if ($this->variable === null) {
			return false;
		}

		$language = Language::ensure('default');
		$variable = $language->variable($this->variable->key());
		return $variable->hasMultipleValues();
	}

	public function load(): Dialog
	{
		return new FormDialog(
			fields: $this->fields(),
			size: 'large',
			value: $this->value()
		);
	}

	public function submit(): bool
	{
		$key      = $this->request->get('key', '');
		$multiple = $this->request->get('multiple', false);

		$value = match ($multiple) {
			true    => $this->request->get('entries', []),
			default => $this->request->get('value', '')
		};

		if ($this->variable === null) {
			LanguageVariable::create($key, $value);

			if ($this->language->isDefault() === false) {
				$this->language->variable($key)->update($value);
			}
		} else {
			$this->variable = $this->variable->update($value);
		}

		return true;
	}

	public function value(): array
	{
		if ($this->variable === null) {
			return [
				'multiple' => false,
			];
		}

		if ($this->hasMultipleValues() === true) {
			return [
				'entries'  => $this->variable->value(),
				'key'      => $this->variable->key(),
				'multiple' => true
			];
		}

		return [
			'key'      => $this->variable->key(),
			'multiple' => false,
			'value'    => $this->variable->value()
		];
	}
}
