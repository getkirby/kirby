<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Controls the Panel dialog to change the title of the site
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class SiteChangeTitleDialogController extends DialogController
{
	public function load(): Dialog
	{
		return new FormDialog(
			fields: [
				'title' => Field::title([
					'required'  => true,
					'preselect' => true
				])
			],
			submitButton: $this->i18n('rename'),
			value: [
				'title' => $this->site->title()->value(),
			]
		);
	}

	public function submit(): array
	{
		$title      = $this->request->get('title');
		$this->site = $this->site->changeTitle($title);

		return [
			'event' => 'site.changeTitle'
		];
	}
}
