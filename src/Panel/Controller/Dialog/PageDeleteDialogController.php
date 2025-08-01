<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Controls the Panel dialog for deleting a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class PageDeleteDialogController extends PageDialogController
{
	public function load(): Dialog
	{
		$text = I18n::template('page.delete.confirm', [
			'title' => Escape::html($this->page->title()->value())
		]);

		if ($this->page->childrenAndDrafts()->count() === 0) {
			return new RemoveDialog(text: $text);
		}

		return new FormDialog(
			fields: [
				'info' => [
					'type'  => 'info',
					'theme' => 'negative',
					'text'  => I18n::translate('page.delete.confirm.subpages')
				],
				'check' => [
					'label'   => I18n::translate('page.delete.confirm.title'),
					'type'    => 'text',
					'counter' => false
				]
			],
			size: 'medium',
			submitButton: [
				'text'  => I18n::translate('delete'),
				'theme' => 'negative'
			],
			text: $text
		);
	}

	public function submit(): array
	{
		$referrer = $this->kirby->panel()->referrer();
		$url      = $this->page->panel()->url(true);

		if (
			$this->page->childrenAndDrafts()->count() > 0 &&
			$this->request->get('check') !== $this->page->title()->value()
		) {
			throw new InvalidArgumentException(key: 'page.delete.confirm');
		}

		$this->page->delete(true);

		// redirect to the parent model URL
		// if the dialog has been opened in the page view
		if ($referrer === $url) {
			$redirect = $this->page->parentModel()->panel()->url(true);
		}

		return [
			'event'    => 'page.delete',
			'redirect' => $redirect ?? null
		];
	}
}
