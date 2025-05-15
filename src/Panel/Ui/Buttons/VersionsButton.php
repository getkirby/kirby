<?php

namespace Kirby\Panel\Ui\Buttons;

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
 * @internal
 */
class VersionsButton extends ViewButton
{
	public function __construct(
		ModelWithContent $model,
		VersionId|string $versionId = 'latest'
	) {
		$versionId = $versionId === 'compare' ? 'compare' : VersionId::from($versionId)->value();
		$viewUrl   = $model->panel()->url(true) . '/preview';

		parent::__construct(
			class: 'k-versions-view-button',
			icon: $versionId === 'compare' ? 'layout-columns' : 'git-branch',
			options: [
				[
					'label'   => I18n::translate('version.latest'),
					'icon'    => 'git-branch',
					'link'    => $viewUrl . '/latest',
					'current' => $versionId === 'latest'
				],
				[
					'label'   => I18n::translate('version.changes'),
					'icon'    => 'git-branch',
					'link'    => $viewUrl . '/changes',
					'current' => $versionId === 'changes'
				],
				'-',
				[
					'label'   => I18n::translate('version.compare'),
					'icon'    => 'layout-columns',
					'link'    => $viewUrl . '/compare',
					'current' => $versionId === 'compare'
				],

			],
			text: I18n::translate('version.' . $versionId),
		);
	}
}
