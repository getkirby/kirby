<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Site;
use Kirby\Toolkit\I18n;
use Override;

/**
 * Controls the preview view for the site
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SitePreviewViewController extends ModelPreviewViewController
{
	public function __construct(
		Site $model,
		string $versionId
	) {
		parent::__construct($model, $versionId);
	}

	#[Override]
	public function props(): array
	{
		return [
			...$props = (new SiteViewController($this->model))->props(),
			...parent::props(),
			'back'      => $props['link'],
			'title'     => I18n::translate('view.site') . ' | ' . I18n::translate('preview'),
		];
	}
}
