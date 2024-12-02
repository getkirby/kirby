<?php

namespace Kirby\Form;

use Kirby\Cms\ModelWithContent;
use Kirby\Exception\Exception;

class FieldWithApiRoutes extends FieldClass
{
	public function api(): array
	{
		return FieldClassTest::apiRoutes();
	}
}

class FieldWithComputedValue extends FieldClass
{
	public function computedValue(): string
	{
		return $this->value . ' computed';
	}
}

class FieldWithCustomStoreHandler extends FieldClass
{
	public function toStoredValue(bool $default = false): mixed
	{
		return implode(',', $this->value);
	}
}

class FieldWithDefaultIcon extends FieldClass
{
	public function icon(): string|null
	{
		return $this->icon ?? 'test';
	}
}

class FieldWithDialogs extends FieldClass
{
	public function dialogs(): array
	{
		return FieldClassTest::dialogRoutes();
	}
}

class FieldWithDrawers extends FieldClass
{
	public function drawers(): array
	{
		return FieldClassTest::drawerRoutes();
	}
}

class FieldWithHiddenFlag extends FieldClass
{
	public function isHidden(): bool
	{
		return true;
	}
}

class FieldWithUnsaveableFlag extends FieldClass
{
	public function isSaveable(): bool
	{
		return false;
	}
}

class FieldWithValidations extends FieldClass
{
	public function maxlength(): int
	{
		return 5;
	}

	public function validations(): array
	{
		return [
			'maxlength',
			'custom' => function ($value) {
				if ($value !== null && $value !== 'a') {
					throw new Exception('Please enter an a');
				}
			}
		];
	}
}

class FieldWithProps extends FieldClass
{
	public function type(): string
	{
		return 'test';
	}
}


/**
 * @coversDefaultClass \Kirby\Form\FieldClass
 */
class FieldClassTest extends FieldTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.FieldClass';

	protected function field(
		array $props = [],
		ModelWithContent|null $model = null
	): Field|FieldClass {
		return new FieldWithProps([
			'model' => $model ?? $this->model,
			'name'  => 'test',
			...$props
		]);
	}

	protected function fieldWithApiRoutes(): Field|FieldClass
	{
		return new FieldWithApiRoutes([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithComputedValue(): Field|FieldClass
	{
		return new FieldWithComputedValue([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithCustomStoreHandler(): Field|FieldClass
	{
		return new FieldWithCustomStoreHandler([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithDefaultIcon(): Field|FieldClass
	{
		return new FieldWithDefaultIcon([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithDialogs(): Field|FieldClass
	{
		return new FieldWithDialogs([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithDrawers(): Field|FieldClass
	{
		return new FieldWithDrawers([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithHiddenFlag(): Field|FieldClass
	{
		return new FieldWithHiddenFlag([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithUnsaveableFlag(): Field|FieldClass
	{
		return new FieldWithUnsaveableFlag([
			'model' => $this->model,
			'name'  => 'test'
		]);
	}

	protected function fieldWithValidations(): Field|FieldClass
	{
		return new FieldWithValidations([
			'model' => $this->model,
			'name'  => 'test',
		]);
	}
}
