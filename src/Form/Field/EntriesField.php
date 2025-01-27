<?php

namespace Kirby\Form\Field;

use InvalidArgumentException;
use Kirby\Data\Data;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;

/**
 * Main class file of the entries field
 *
 * @package   Kirby Field
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class EntriesField extends FieldClass
{
	use EmptyState;
	use Max;
	use Min;

	protected array $field;
	protected bool  $sortable = true;

	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->setEmpty($params['empty'] ?? null);
		$this->setField($params['field'] ?? null);
		$this->setMax($params['max'] ?? null);
		$this->setMin($params['min'] ?? null);
		$this->setSortable($params['sortable'] ?? true);
	}

	public function field(): array
	{
		return $this->field;
	}

	public function fill(mixed $value = null): void
	{
		parent::fill(Data::decode($value, 'yaml'));
	}

	public function form(array $values = []): Form
	{
		return new Form([
			'fields' => [$this->field()],
			'values' => $values,
			'model'  => $this->model
		]);
	}

	public function props(): array
	{
		return [
				'empty'    => $this->empty(),
				'field'    => $this->field(),
				'max'      => $this->max(),
				'min'      => $this->min(),
				'sortable' => $this->sortable(),
			] + parent::props();
	}

	protected function setField(array|string|null $attrs = null): void
	{
		if (is_string($attrs) === true) {
			$attrs = ['type' => $attrs];
		}

		$attrs ??= ['type' => 'text'];

		if (in_array($attrs['type'], $this->supports()) === false) {
			throw new InvalidArgumentException(
				key: 'entries.supports',
				data: ['type' => $attrs['type']]
			);
		}

		$this->field = $attrs;
	}

	public function supports(): array
	{
		return [
			"color",
			"date",
			"list",
			"multiselect",
			"number",
			"range",
			"select",
			"slug",
			"tags",
			"tel",
			"text",
			"textarea",
			"time",
			"url",
			"writer",
		];
	}

	protected function setSortable(bool|null $sortable = true): void
	{
		$this->sortable = $sortable;
	}

	public function sortable(): bool
	{
		return $this->sortable;
	}

	public function toFormValue(bool $default = false): mixed
	{
		$value = parent::toFormValue($default);

		if ($value === null) {
			return null;
		}

		return Data::decode($value, 'yaml');
	}

	public function toStoredValue(bool $default = false): mixed
	{
		$value = parent::toStoredValue($default);

		if ($value === null) {
			return null;
		}

		return Data::encode($value, 'yaml');
	}

	public function validations(): array
	{
		return [
			'min',
			'max'
		];
	}
}
