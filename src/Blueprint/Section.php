<?php

namespace Kirby\Blueprint;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\SectionField;
use Kirby\Toolkit\Component;

/**
 * Section
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Section extends Component
{
	/**
	 * Registry for all component mixins
	 */
	public static array $mixins = [];

	/**
	 * Registry for all component types
	 */
	public static array $types = [];

	/**
	 * The field that wraps this section, if the section
	 * has been defined as a `section` field
	 * @since 6.0.0
	 */
	protected SectionField|null $field = null;

	/**
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(string $type, array $attrs = [])
	{
		if (isset($attrs['model']) === false) {
			throw new InvalidArgumentException(
				message: 'Undefined section model'
			);
		}

		if ($attrs['model'] instanceof ModelWithContent === false) {
			throw new InvalidArgumentException(
				message: 'Invalid section model'
			);
		}

		// use the type as fallback for the name
		$attrs['name'] ??= $type;
		$attrs['type']   = $type;

		parent::__construct($type, $attrs);
	}

	/**
	 * Returns field api call
	 */
	public function api(): mixed
	{
		if (
			isset($this->options['api']) === true &&
			$this->options['api'] instanceof Closure
		) {
			return $this->options['api']->call($this);
		}

		return null;
	}

	/**
	 * Returns optional dialog routes for the field
	 * @since 6.0.0
	 */
	public function dialogs(): array
	{
		if (isset($this->options['dialogs']) === false) {
			return [];
		}

		if ($this->options['dialogs'] instanceof Closure) {
			return $this->options['dialogs']->call($this);
		}

		throw new InvalidArgumentException(
			message: 'Dialogs of section "' . $this->name() . '" must be defined as a closure'
		);
	}

	/**
	 * Returns optional drawer routes for the field
	 * @since 6.0.0
	 */
	public function drawers(): array
	{
		if (isset($this->options['drawers']) === false) {
			return [];
		}

		if ($this->options['drawers'] instanceof Closure) {
			return $this->options['drawers']->call($this);
		}

		throw new InvalidArgumentException(
			message: 'Drawers of section "' . $this->name() . '" must be defined as a closure'
		);
	}

	public function errors(): array
	{
		if (array_key_exists('errors', $this->methods) === true) {
			return $this->methods['errors']->call($this);
		}

		return $this->errors ?? [];
	}

	/**
	 * Returns the wrapping field, if the section has been
	 * defined as a `section` field
	 * @since 6.0.0
	 */
	public function field(): SectionField|null
	{
		return $this->field;
	}

	public function kirby(): App
	{
		return $this->model()->kirby();
	}

	public function model(): ModelWithContent
	{
		return $this->model;
	}

	public function toArray(): array
	{
		$array = parent::toArray();

		unset($array['field'], $array['model']);

		return $array;
	}

	public function toResponse(): array
	{
		return [
			'status' => 'ok',
			'code'   => 200,
			'name'   => $this->name,
			'type'   => $this->type,
			...$this->toArray()
		];
	}
}
