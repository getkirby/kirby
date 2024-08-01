<?php

namespace Kirby\Cms;

/**
 * PageBlueprint
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PageBlueprint extends Blueprint
{
	/**
	 * Creates a new page blueprint object
	 * with the given props
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		// normalize all available page options
		$this->props['options'] = $this->normalizeOptions(
			$this->props['options'] ?? true,
			// defaults
			[
				'access'     	 => null,
				'changeSlug'     => null,
				'changeStatus'   => null,
				'changeTemplate' => null,
				'changeTitle'    => null,
				'create'         => null,
				'delete'         => null,
				'duplicate'      => null,
				'list'           => null,
				'move'           => null,
				'preview'        => null,
				'read'           => null,
				'sort'           => null,
				'update'         => null,
			],
			// aliases (from v2)
			[
				'status'   => 'changeStatus',
				'template' => 'changeTemplate',
				'title'    => 'changeTitle',
				'url'      => 'changeSlug',
			]
		);

		// normalize the ordering number
		$this->props['num'] = $this->normalizeNum($this->props['num'] ?? 'default');

		// normalize the available status array
		$this->props['status'] = $this->normalizeStatus($this->props['status'] ?? null);
	}

	/**
	 * Returns the page numbering mode
	 */
	public function num(): string
	{
		return $this->props['num'];
	}

	/**
	 * Normalizes the ordering number
	 *
	 * @param mixed $num
	 */
	protected function normalizeNum($num): string
	{
		$aliases = [
			'0'    => 'zero',
			'sort' => 'default',
		];

		return $aliases[$num] ?? $num;
	}

	/**
	 * Normalizes the available status options for the page
	 *
	 * @param mixed $status
	 */
	protected function normalizeStatus($status): array
	{
		$defaults = [
			'draft'    => [
				'label' => $this->i18n('page.status.draft'),
				'text'  => $this->i18n('page.status.draft.description'),
			],
			'unlisted' => [
				'label' => $this->i18n('page.status.unlisted'),
				'text'  => $this->i18n('page.status.unlisted.description'),
			],
			'listed' => [
				'label' => $this->i18n('page.status.listed'),
				'text'  => $this->i18n('page.status.listed.description'),
			]
		];

		// use the defaults, when the status is not defined
		if (empty($status) === true) {
			$status = $defaults;
		}

		// extend the status definition
		$status = $this->extend($status);

		// clean up and translate each status
		foreach ($status as $key => $options) {
			// skip invalid status definitions
			if (in_array($key, ['draft', 'listed', 'unlisted']) === false || $options === false) {
				unset($status[$key]);
				continue;
			}

			if ($options === true) {
				$status[$key] = $defaults[$key];
				continue;
			}

			// convert everything to a simple array
			if (is_array($options) === false) {
				$status[$key] = [
					'label' => $options,
					'text'  => null
				];
			}

			// always make sure to have a proper label
			if (empty($status[$key]['label']) === true) {
				$status[$key]['label'] = $defaults[$key]['label'];
			}

			// also make sure to have the text field set
			$status[$key]['text'] ??= null;

			// translate text and label if necessary
			$status[$key]['label'] = $this->i18n($status[$key]['label'], $status[$key]['label']);
			$status[$key]['text']  = $this->i18n($status[$key]['text'], $status[$key]['text']);
		}

		// the draft status is required
		if (isset($status['draft']) === false) {
			$status = ['draft' => $defaults['draft']] + $status;
		}

		// remove the draft status for the home and error pages
		if ($this->model->isHomeOrErrorPage() === true) {
			unset($status['draft']);
		}

		return $status;
	}

	/**
	 * Returns the options object
	 * that handles page options and permissions
	 */
	public function options(): array
	{
		return $this->props['options'];
	}

	/**
	 * Returns the preview settings
	 * The preview setting controls the "Open"
	 * button in the panel and redirects it to a
	 * different URL if necessary.
	 */
	public function preview(): string|bool
	{
		$preview = $this->props['options']['preview'] ?? true;

		if (is_string($preview) === true) {
			return $this->model->toString($preview);
		}

		return $this->model->permissions()->can('preview', true);
	}

	/**
	 * Returns the status array
	 */
	public function status(): array
	{
		return $this->props['status'];
	}
}
