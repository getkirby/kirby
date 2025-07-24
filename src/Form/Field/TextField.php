<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;
use Kirby\Form\Mixin\Converter;
use Kirby\Form\Mixin\Counter;
use Kirby\Form\Mixin\Font;
use Kirby\Form\Mixin\Maxlength;
use Kirby\Form\Mixin\Minlength;
use Kirby\Form\Mixin\Pattern;
use Kirby\Form\Mixin\Spellcheck;

/**
 * Main class file of the text field
 *
 * @package   Kirby Field
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextField extends FieldClass
{
	use Counter;
	use Converter;
	use Font;
	use Maxlength;
	use Minlength;
	use Pattern;
	use Spellcheck;

	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->setConverter($params['converter'] ?? null);
		$this->setCounter($params['counter'] ?? true);
		$this->setFont($params['font'] ?? null);
		$this->setMaxlength($params['maxlength'] ?? null);
		$this->setMinlength($params['minlength'] ?? null);
		$this->setPattern($params['pattern'] ?? null);
		$this->setSpellcheck($params['spellcheck'] ?? false);
	}

	public function default(): mixed
	{
		return $this->convert(parent::default());
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'converter'  => $this->converter(),
			'counter'    => $this->counter(),
			'font'       => $this->font(),
			'maxlength'  => $this->maxlength(),
			'minlength'  => $this->minlength(),
			'pattern'    => $this->pattern(),
			'spellcheck' => $this->spellcheck(),
		];
	}

	public function toFormValue(): mixed
	{
		return (string)$this->convert($this->value);
	}

	public function validations(): array
	{
		return [
			'minlength',
			'maxlength',
			'pattern'
		];
	}
}
