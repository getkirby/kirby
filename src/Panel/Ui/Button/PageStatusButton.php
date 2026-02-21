<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\Page;

/**
 * Status view button for pages
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class PageStatusButton extends ViewButton
{
	public function __construct(
		Page $page
	) {
		$status    = $page->status();
		$blueprint = $page->blueprint()->status()[$status] ?? null;
		$disabled  = $page->permissions()->cannot('changeStatus') || $page->lock()->isLocked();

		$text   = $blueprint['label'] ?? null;
		$text ??= $this->i18n('page.status.' . $status);
		$title  = $this->i18n('page.status') . ': ' . $text;

		if ($disabled === true) {
			$title .= ' (' . $this->i18n('disabled') . ')';
		}

		parent::__construct(
			class: 'k-status-view-button k-page-status-button',
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
