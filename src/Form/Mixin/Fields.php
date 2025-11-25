<?php

namespace Kirby\Form\Mixin;

use Kirby\Form\Form;

trait Fields
{
	/**
	 * Fields setup for the form. Works just like fields in regular forms.
	 */
	protected array|null $fields;

	/**
	 * Cache for the form instance
	 */
	protected Form $form;

	/**
	 * Returns the props for all fields in the form
	 */
	public function fields(): array
	{
		if ($this->fields === null || $this->fields === []) {
			return [];
		}

		return $this->form()->fields()->toProps();
	}

	/**
	 * Creates and caches the form instance with all fields
	 */
	public function form(): Form
	{
		$this->form ??= new Form(
			fields: $this->fields ?? [],
			model: $this->model(),
			language: 'current'
		);

		return $this->form->reset();
	}

	protected function setFields(array|null $fields): void
	{
		$this->fields = $fields;
	}
}
