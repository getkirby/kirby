<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;

/**
 * Unknown fields are fields that are not part of the blueprint
 * schema but are submitted or filled in. This can happen if
 * a model has additional fields that are not managed via the
 * Panel. E.g. they are only stored in the text file, but should not
 * be shown and modified by the editors.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since 5.0.0
 * @internal
 */
class UnknownField extends FieldClass
{
	public function __construct(string $name)
	{
		parent::__construct([
			'name' => $name,
		]);
	}

	public function props(): array
	{
		return [
			'name'   => $this->name(),
			'hidden' => $this->isHidden(),
		];
	}

	public function isHidden(): bool
	{
		return true;
	}

	public function isSaveable(): bool
	{
		return true;
	}

	public function type(): string
	{
		return 'unknown';
	}
}
