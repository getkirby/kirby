<?php

namespace Kirby\Panel;

use Kirby\Exception\InvalidArgumentException;

class PageTypeDialog
{
	protected string $parentId;

	public function __construct(
		string|null $parentId,
	) {
		$this->parentId = $parentId ?? 'site';
	}

	/**
	 * Provides all the props for the
	 * dialog, including the fields and
	 * initial values
	 */
	public function load(array $blueprints): array
	{
		return [
			'component' => 'k-form-dialog',
			'props' => [
				'fields' => [
					'blueprint' => Field::template($blueprints, [
						'required' => true
					]),
					'parent' => Field::hidden(),
				],
				'submitButton' => 'Select',
				'value' => [
					'parent'    => $this->parentId,
					'blueprint' => $blueprints[0]['name']
				]
			]
		];
	}

	/**
	 * Submits the dialog and redirects to the page.create dialog
	 */
	public function submit(array $input): array
	{
		if (empty($input['blueprint']) === true) {
			throw new InvalidArgumentException('Please choose a template');
		}

		return [
			'redirect' => [
				'url'   => 'pages/create',
				'type'  => 'dialog',
				'query' => [
					'parent'   => $this->parentId,
					'template' => $input['blueprint']
				]
			]
		];
	}
}
