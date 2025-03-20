<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class InfoField extends FieldClass
{
	protected string|null $text;
	protected string|null $theme;

	public function __construct(array $params)
	{
		parent::__construct($params);

		$this->text  = $params['text'] ?? null;
		$this->theme = $params['theme'] ?? null;
	}

	public function isSaveable(): bool
	{
		return false;
	}

	public function props(): array
	{
		$props = parent::props();

		// Unset unnecessary inherited props
		unset(
			$props['after'],
			$props['autofocus'],
			$props['before'],
			$props['default'],
			$props['disabled'],
			$props['placeholder'],
			$props['required'],
			$props['translate']
		);

		return [
			...$props,
			'text'  => $this->text(),
			'theme' => $this->theme()
		];
	}

	public function text(): string|null
	{
		if ($text = $this->i18n($this->text, $this->text)) {
			$text = $this->model()->toSafeString($text);
			$text = $this->kirby()->kirbytext($text);
		}

		return $text;
	}

	public function theme(): string|null
	{
		return $this->theme;
	}
}
