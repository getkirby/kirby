<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Form\FieldClass;
use Throwable;

/**
 * Exception fields are internal fields that replace a broken field
 * to help debug the issue by displaying a useful error message
 * in the Panel. The use the info field component to display the error message.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since 5.0.0
 * @internal
 */
class ExceptionField extends FieldClass
{
	public function __construct(
		string $name,
		protected Throwable $exception
	) {
		$this->name = $name;
	}

	public function hasValue(): bool
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
