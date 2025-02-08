<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Cms\Site;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * Dialog to change the title of the site
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class SiteChangeTitleDialog extends FormDialog
{
	protected Site $site;

	public function __construct()
	{
		parent::__construct(
			fields: [
				'title' => Field::title([
					'required'  => true,
					'preselect' => true
				])
			],
			submitButton: I18n::translate('rename'),
			value: [
				'title' => App::instance()->site()->title()->value(),
			]
		);

		$this->site = $this->kirby->site();
	}

	public function submit(): array
	{
		$title      = $this->request->get('title');
		$this->site = $this->site->changeTitle($title);

		return [
			'event' => 'site.changeTitle',
		];
	}
}
