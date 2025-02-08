<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\I18n;

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
		$fields['key']['disabled']    = true;
		$fields['value']['autofocus'] = true;

		// shows info text when variable is an array
		// TODO: 5.0: use entries field instead showing info text
		if ($this->isVariableArray() === true) {
			$fields['value'] = [
				'label' => I18n::translate('info'),
				'type'  => 'info',
				'text'  => 'You are using an array variable for this key. Please modify it in the language file in /site/languages',
			];
		}

		return $fields;
	}

	public function isVariableArray(): bool
	{
		return is_array($this->variable->value()) === true;
	}

	public function submit(): true
	{
		$value          = $this->request->get('value', '');
		$this->variable = $this->variable->update($value);

		return true;
	}

	public function value(): array
	{
		return [
			'key'   => $this->variable->key(),
			'value' => $this->variable->value()
		];
	}
}
