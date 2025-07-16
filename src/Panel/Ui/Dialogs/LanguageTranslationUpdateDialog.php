<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Language;
use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;

/**
 * Dialog to update a language variable
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguageTranslationUpdateDialog extends LanguageTranslationCreateDialog
{
	use IsForLanguageVariable;

	public function __construct(
		public LanguageVariable $variable
	) {
		if ($this->variable->exists() === false) {
			throw new NotFoundException(
				key: 'language.variable.notFound'
			);
		}

		parent::__construct(
			language: $this->variable->language(),
			cancelButton: $this->isVariableArray() === false,
			submitButton: $this->isVariableArray() === false
		);
	}

	public function fields(): array
	{
		$fields = parent::fields();

		// the key field cannot be changed
		// the multiple field is hidden
		$fields['key']['disabled']  = true;
		$fields['multiple']['type'] = 'hidden';

		// set focus on the correct value field
		if ($this->isVariableArray() === true) {
			$fields['entries']['autofocus'] = true;
		} else {
			$fields['value']['autofocus'] = true;
		}

		return $fields;
	}

	/**
	 * Check if the variable has multiple values;
	 * ensure to use the default language for this check because
	 * the variable might not exist in the current language but
	 * already be defined in the default language with multiple values
	 */
	public function isVariableArray(): bool
	{
		return Language::ensure('default')->variable($this->variable->key(), true)->hasMultipleValues();
	}

	public function submit(): true
	{
		$multiple = $this->request->get('multiple', false);
		$value    = match ($multiple) {
			true    => $this->request->get('entries', []),
			default => $this->request->get('value', '')
		};
		$this->variable = $this->variable->update($value);

		return true;
	}

	public function value(): array
	{
		// when value is string, set value for value field
		// when value is array, set value for entries field
		if ($this->isVariableArray() === true) {
			return [
				'entries'  => $this->variable->value(),
				'key'      => $this->variable->key(),
				'multiple' => true
			];
		}

		return [
			'key'      => $this->variable->key(),
			'value'    => $this->variable->value(),
			'multiple' => false
		];
	}
}
