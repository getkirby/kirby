<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\File;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class FileChangeNameDialog extends FormDialog
{
	use IsForFile;

	public function __construct(
		public File $file
	) {
		parent::__construct(
			fields: [
				'name' => [
					'label'     => I18n::translate('name'),
					'type'      => 'slug',
					'required'  => true,
					'icon'      => 'title',
					'allow'     => 'a-z0-9@._-',
					'after'     => '.' . $this->file->extension(),
					'preselect' => true
				]
			],
			submitButton: I18n::translate('rename'),
			value: [
				'name' => $this->file->name(),
			]
		);
	}

	public function submit(): array
	{
		$name       = $this->request->get('name');
		$oldUrl     = $this->file->panel()->url(true);
		$this->file = $this->file->changeName($name);
		$newUrl     = $this->file->panel()->url(true);

		$response = [
			'event' => 'file.changeName'
		];

		// check for a necessary redirect after the filename has changed
		if (Panel::referrer() === $oldUrl && $oldUrl !== $newUrl) {
			$response['redirect'] = $newUrl;
		}

		return $response;
	}
}
