<?php

namespace Kirby\Panel\Controller\View;

/**
 * Controls the remote preview view
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RemotePreviewViewController extends PreviewViewController
{
	protected function path(): string
	{
		return 'preview/' . $this->mode . '/remote';
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'component' => 'k-remote-preview-view',
			'title'     =>  $this->i18n('preview'),
		];
	}
}
