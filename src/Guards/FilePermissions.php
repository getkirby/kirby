<?php

namespace Kirby\Guards;

use Kirby\Cms\File;

/**
 * Role and blueprint based permissions for a `$file` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelPermissions<File>
 */
class FilePermissions extends ModelPermissions
{
	public function category(): string
	{
		return 'files';
	}

	protected function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'file.' . $key . '.permission',
			data: [
				'filename' => $this->model->filename(),
				'id'       => $this->model->id(),
				...$data
			]
		);
	}
}
