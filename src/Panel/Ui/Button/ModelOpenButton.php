<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\Page;
use Kirby\Cms\Site;

/**
 * Open button for pages and site
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class ModelOpenButton extends OpenButton
{
	public function __construct(
		Page|Site $model,
		string $mode = 'latest'
	) {
		$mode = match ($mode) {
			'compare' => 'changes',
			default   => $mode
		};

		parent::__construct(
			link: $model->previewUrl($mode)
		);
	}

	public function render(): array|null
	{
		if ($this->link === null) {
			return null;
		}

		return parent::render();
	}
}
