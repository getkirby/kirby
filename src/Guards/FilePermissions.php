<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\Model;

/**
 * Role and blueprint based permissions for a `$file` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FilePermissions extends ModelPermissions
{
	/**
	 * @var File
	 */
	protected Model $model;

	public function category(): string
	{
		return 'files';
	}

	public function error(string $key, array $data = []): never
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
