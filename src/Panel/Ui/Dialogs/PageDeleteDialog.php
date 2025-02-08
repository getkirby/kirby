<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Renderable;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Dialog to delete a page
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PageDeleteDialog extends Renderable
{
	use IsForPage;

	public function __construct(
		public Page $page
	) {
	}

	public function render(): array
	{
		$text = I18n::template('page.delete.confirm', [
			'title' => Escape::html($this->page->title()->value())
		]);

		if ($this->page->childrenAndDrafts()->count() === 0) {
			return (new RemoveDialog(text: $text))->render();
		}

		$dialog = new FormDialog(
			fields: [
				'info' => [
					'type'  => 'info',
					'theme' => 'negative',
					'text'  => I18n::translate('page.delete.confirm.subpages')
				],
				'confirm' => [
					'label'   => I18n::translate('page.delete.confirm.title'),
					'type'    => 'text',
					'counter' => false
				]
			],
			submitButton: [
				'text'  => I18n::translate('delete'),
				'theme' => 'negative'
			],
			text: $text
		);

		return $dialog->render();
	}

	public function submit(): array
	{
		$confirm = $this->page->kirby()->request()->get('confirm');

		if (
			$this->page->childrenAndDrafts()->count() > 0 &&
			$confirm !== $this->page->title()->value()
		) {
			throw new InvalidArgumentException(
				key: 'page.delete.confirm'
			);
		}

		$url      = $this->page->panel()->url(true);
		$referrer = Panel::referrer();
		$this->page->delete(true);

		$response = [
			'event' => 'page.delete'
		];

		// redirect to the parent model URL
		// if the dialog has been opened in the page view
		if ($url === $referrer) {
			$response['redirect'] = $this->page->parentModel()->panel()->url(true);
		}

		return $response;
	}
}
