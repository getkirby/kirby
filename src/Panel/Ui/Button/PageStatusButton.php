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
		$disabled  = $page->guards()->isAvailable('changeStatus') === false || $page->lock()->isLocked() === true;

		$text   = $blueprint['label'] ?? null;
		$text ??= $this->i18n('page.status.' . $status);
		$title  = $this->i18n('page.status') . ': ' . $text;

		$theme   = $blueprint['theme'] ?? null;
		$theme ??= match ($status) {
			'draft'    => 'negative',
			'unlisted' => 'info',
			'listed'   => 'positive'
		};

		if ($disabled === true) {
			$title .= ' (' . $this->i18n('disabled') . ')';
		}

		parent::__construct(
			class: 'k-status-view-button k-page-status-button',
			dialog: $page->panel()->url(true) . '/changeStatus',
			disabled: $disabled,
			icon: $blueprint['icon'] ?? 'status-' . $status,
			style: '--icon-size: 15px',
			text: $text,
			title: $title,
			theme: $theme . '-icon'
		);
	}
}
