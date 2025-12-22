<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\ModelWithContent;
use Kirby\Content\VersionId;
use Kirby\Toolkit\I18n;

/**
 * Versions view button for models
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @unstable
 */
class VersionsButton extends ViewButton
{
	public function __construct(
		ModelWithContent $model,
		public string $mode = 'latest'
	) {
		parent::__construct(
			model: $model,
			class: 'k-versions-view-button',
			icon: $this->icon(),
			text: $this->i18n('version.' . $this->mode()),
		);
	}

	/**
	 * Returns the button icon based on the view's mode
	 * @since 6.0.0
	 */
	protected function icon(): string
	{
		return match ($this->mode) {
			'compare' => 'layout-columns',
			'form'    => 'edit-line',
			default   => 'git-branch'
		};
	}

	/**
	 * Whether the given mode is the current mode
	 * @since 6.0.0
	 */
	public function isCurrent(string $mode): bool
	{
		return $this->mode() === $mode;
	}

	/**
	 * Returns the view's mode using the proper
	 * values for version IDs
	 * @since 6.0.0
	 */
	public function mode(): string
	{
		return match ($this->mode) {
			'compare' => 'compare',
			'form'    => 'form',
			default   => VersionId::from($this->mode)->value()
		};
	}

	/**
	 * Returns the options for the dropdown
	 * @since 6.0.0
	 */
	public function options(): array
	{
		return $this->options ??= [
			[
				'label'   => $this->i18n('version.form'),
				'icon'    => 'edit-line',
				'link'    => $this->url('form'),
				'current' => $this->isCurrent('form')
			],
			'-',
			[
				'label'   => $this->i18n('version.compare'),
				'icon'    => 'layout-columns',
				'link'    => $this->url('compare'),
				'current' => $this->isCurrent('compare')
			],
			'-',
			[
				'label'   => I18n::translate('version.latest'),
				'icon'    => 'git-branch',
				'link'    => $this->url('latest'),
				'current' => $this->isCurrent('latest')
			],
			[
				'label'   => I18n::translate('version.changes'),
				'icon'    => 'git-branch',
				'link'    => $this->url('changes'),
				'current' => $this->isCurrent('changes')
			]
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'options' => $this->options()
		];
	}

	/**
	 * Returns the preview view URL for the given version ID
	 * @since 6.0.0
	 */
	public function url(string $mode): string
	{
		return $this->model->panel()->url(true) . '/preview/' . $mode;
	}
}
