<?php

use Kirby\Cms\File;
use Kirby\Exception\PermissionException;
use Kirby\Form\Form;

// routing pattern to match all models with files
$filePattern   = '(account/|pages/[^/]+/|site/|users/[^/]+/|)files/(:any)';
$parentPattern = '(account|pages/[^/]+|site|users/[^/]+)/files';

/**
 * Files Routes
 */
return [
	[
		'pattern' => $filePattern . '/fields/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $parent, string $filename, string $fieldName, string|null $path = null) {
			if ($file = $this->file($parent, $filename)) {
				$field = Form::for($file)->field($fieldName);
				return $this->forward($field, $path);
			}
		}
	],
	[
		'pattern' => $parentPattern,
		'method'  => 'GET',
		'action'  => function (string $path) {
			return $this->files($path)->sorted();
		}
	],
	[
		'pattern' => $parentPattern,
		'method'  => 'POST',
		'action'  => function (string $path) {
			// move_uploaded_file() not working with unit test
			// @codeCoverageIgnoreStart
			$parent = $this->parent($path);

			return $this->upload(
				callback: function ($source, $filename) use ($parent) {
					return $parent->createFile([
						'content' => [
							'sort' => $this->request()->body()->int('sort')
						],
						'source'   => $source,
						'template' => $this->request()->body()->string('template'),
						'filename' => $filename
					], move: true);
				},
				preflight: function (string $filename, string|null $template) use ($parent) {
					$file = new File([
						'parent'   => $parent,
						'filename' => $filename,
						'template' => $template
					]);

					if ($file->guards()->isAvailable('create') !== true) {
						throw new PermissionException(
							message: 'The file cannot be created'
						);
					}
				}
			);
			// @codeCoverageIgnoreEnd
		}
	],
	[
		'pattern' => $parentPattern . '/search',
		'method'  => 'GET|POST',
		'action'  => function (string $path) {
			$files = $this->files($path);

			if ($this->request()->method() === 'GET') {
				return $files->search($this->request()->query()->string('q'));
			}

			return $files->query(array_filter([
				'limit'    => $this->request()->body()->int('limit'),
				'offset'   => $this->request()->body()->int('offset'),
				'paginate' => $this->request()->body()->get('paginate'),
				'search'   => $this->request()->body()->get('search'),
			], fn ($value) => $value !== null));
		}
	],
	[
		'pattern' => 'files/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			$files = $this
				->site()
				->index(true)
				->filter('isListable', true)
				->files()
				->filter('isListable', true);

			if ($this->request()->method() === 'GET') {
				return $files->search($this->request()->query()->string('q'));
			}

			return $files->query(array_filter([
				'limit'    => $this->request()->body()->int('limit'),
				'offset'   => $this->request()->body()->int('offset'),
				'paginate' => $this->request()->body()->get('paginate'),
				'search'   => $this->request()->body()->get('search'),
			], fn ($value) => $value !== null));
		}
	],
	[
		'pattern' => $parentPattern . '/sort',
		'method'  => 'PATCH',
		'action'  => function (string $path) {
			return $this->files($path)->changeSort(
				$this->request()->body()->array('files', []),
				$this->request()->body()->int('index', 0)
			);
		}
	],
	[
		'pattern' => $filePattern,
		'method'  => 'GET',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename);
		}
	],
	[
		'pattern' => $filePattern,
		'method'  => 'PATCH',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename)->update(
				$this->request()->body()->toArray(),
				$this->language(),
				true
			);
		}
	],
	[
		'pattern' => $filePattern,
		'method'  => 'POST',
		'action'  => function (string $path, string $filename) {
			$file = $this->file($path, $filename);

			return $this->upload(
				callback: fn ($source) => $file->replace($source, move: true),
				preflight: function () use ($file) {
					if ($file->guards()->isAvailable('replace') !== true) {
						throw new PermissionException(
							message: 'The file cannot be replaced'
						);
					}
				}
			);
		}
	],
	[
		'pattern' => $filePattern,
		'method'  => 'DELETE',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename)->delete();
		}
	],
	[
		'pattern' => $filePattern . '/name',
		'method'  => 'PATCH',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename)->changeName($this->request()->body()->string('name', ''));
		}
	],
];
