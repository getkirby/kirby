<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldClass;
use Kirby\Toolkit\Str;

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
	protected string|null $converter;
	protected bool        $counter;
	protected string      $font;
	protected int|null    $maxlength;
	protected int|null    $minlength;
	protected string|null $pattern;
	protected bool        $spellcheck;

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

	public function converter(): string|null
	{
		return $this->converter;
	}

	public function converters(): array
	{
		return [
			'lower'   => function ($value) {
				return Str::lower($value);
			},
			'slug'    => function ($value) {
				return Str::slug($value);
			},
			'ucfirst' => function ($value) {
				return Str::ucfirst($value);
			},
			'upper'   => function ($value) {
				return Str::upper($value);
			},
		];
	}

	public function convert(mixed $value): mixed
	{
		if ($this->converter() === null) {
			return $value;
		}

		$converter = $this->converters()[$this->converter()];

		if (is_array($value) === true) {
			return array_map($converter, $value);
		}

		return call_user_func($converter, trim($value ?? ''));
	}

	public function counter(): bool
	{
		return $this->counter;
	}

	public function default(): mixed
	{
		return $this->convert(parent::default());
	}

	public function font(): string
	{
		return $this->font;
	}

	public function maxlength(): int|null
	{
		return $this->maxlength;
	}

	public function minlength(): int|null
	{
		return $this->minlength;
	}

	public function pattern(): string|null
	{
		return $this->pattern;
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

	protected function setConverter(string|null $converter = null): void
	{
		if (
			$converter !== null &&
			array_key_exists($converter, $this->converters()) === false
		) {
			throw new InvalidArgumentException(
				key: 'field.converter.invalid',
				data: ['converter' => $converter]
			);
		}

		$this->converter = $converter;
	}

	protected function setCounter(bool $counter = true): void
	{
		$this->counter = $counter;
	}

	protected function setFont(string|null $font = null): void
	{
		$this->font = $font === 'monospace' ? 'monospace' : 'sans-serif';
	}

	protected function setMaxlength(int|null $maxlength = null): void
	{
		$this->maxlength = $maxlength;
	}

	protected function setMinlength(int|null $minlength = null): void
	{
		$this->minlength = $minlength;
	}

	protected function setPattern(string|null $pattern = null): void
	{
		$this->pattern = $pattern;
	}

	protected function setSpellcheck(bool $spellcheck = false): void
	{
		$this->spellcheck = $spellcheck;
	}

	public function spellcheck(): bool
	{
		return $this->spellcheck;
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

	/**
	 * Returns the config file path for backward compatibility
	 * Used when plugins extend this field using 'extends' => 'text'
	 *
	 * @todo 8.0 Remove this method when no longer supporting array-based component definitions
	 */
	public static function getConfigFilePath(): string
	{
		return dirname(__DIR__, 3) . '/config/fields/text.php';
	}
}
