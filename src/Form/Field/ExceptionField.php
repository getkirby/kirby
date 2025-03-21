<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Form\FieldClass;
use Throwable;

class ExceptionField extends FieldClass
{
	public function __construct(
		string $name,
		protected Throwable $exception
	) {
		$this->name = $name;
	}

	public function isSaveable(): bool
	{
		return false;
	}

	public function label(): string
	{
		return 'Error in "' . $this->name() . '" field.';
	}

	public function props(): array
	{
		return [
			'label' => $this->label(),
			'name'  => $this->name(),
			'text'  => $this->text(),
			'theme' => $this->theme(),
			'type'  => $this->type(),
		];
	}

	public function text(): string
	{
		$message = $this->exception->getMessage();

		if (App::instance()->option('debug') === true) {
			$message .= ' in file: ' . $this->exception->getFile();
			$message .= ' line: ' . $this->exception->getLine();
		}

		return strip_tags($message);
	}

	public function theme(): string
	{
		return 'negative';
	}

	public function type(): string
	{
		return 'info';
	}
}
