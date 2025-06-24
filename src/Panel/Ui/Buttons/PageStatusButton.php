<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Page;
use Kirby\Toolkit\I18n;

/**
 * Status view button for pages
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @unstable
 */
class PageStatusButton extends ViewButton
{
	public function __construct(
		Page $page
	) {
		$status    = $page->status();
		$blueprint = $page->blueprint()->status()[$status] ?? null;
		$disabled  = $page->permissions()->cannot('changeStatus');
		$text      = $blueprint['label'] ?? I18n::translate('page.status.' . $status);
		$title     = I18n::translate('page.status') . ': ' . $text;

		if ($disabled === true) {
			$title .= ' (' . I18n::translate('disabled') . ')';
		}

		parent::__construct(
			class: 'k-status-view-button k-page-status-button',
			component: 'k-status-view-button',
			dialog: $page->panel()->url(true) . '/changeStatus',
			disabled: $disabled,
			icon: 'status-' . $status,
			style: '--icon-size: 15px',
			text: $text,
			title: $title,
			theme: match($status) {
				'draft'    => 'negative-icon',
				'unlisted' => 'info-icon',
				'listed'   => 'positive-icon'
			}
		);
	}
}
