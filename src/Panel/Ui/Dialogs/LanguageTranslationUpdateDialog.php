<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;

/**
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

		parent::__construct($this->variable->language());
	}

	public function fields(): array
	{
		$fields = parent::fields();
		$fields['key']['disabled']    = true;
		$fields['value']['autofocus'] = true;
		return $fields;
	}

	public function submit(): true
	{
		$value = $this->request->get('value', '');
		$this->variable->update($value);

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
