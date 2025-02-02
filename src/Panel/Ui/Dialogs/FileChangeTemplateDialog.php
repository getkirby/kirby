<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\File;
use Kirby\Panel\Field;
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
class FileChangeTemplateDialog extends FormDialog
{
	use IsForFile;

	public function __construct(
		public File $file
	) {
		$blueprints = $this->file->blueprints();

		parent::__construct(
			fields: [
				'warning' => [
					'type'  => 'info',
					'theme' => 'notice',
					'text'  => I18n::translate('file.changeTemplate.notice')
				],
				'template' => Field::template($blueprints, [
					'required' => true
				])
			],
			submitButton: [
				'text'  => I18n::translate('change'),
				'theme' => 'notice'
			],
			value: [
				'template' => $this->file->template()
			]
		);
	}

	public function submit(): array
	{
		$template = $this->request->get('template');
		$this->file->changeTemplate($template);

		return [
			'event' => 'file.changeTemplate',
		];
	}
}
