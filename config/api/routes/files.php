<?php

use Kirby\Cms\File;
use Kirby\Exception\PermissionException;

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
				return $this->fieldApi($file, $fieldName, $path);
			}
		}
	],
	[
		'pattern' => $filePattern . '/sections/(:any)',
		'method'  => 'GET',
		'action'  => function (string $path, string $filename, string $sectionName) {
			return $this->file($path, $filename)->blueprint()->section($sectionName)?->toResponse();
		}
	],
	[
		'pattern' => $filePattern . '/sections/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $parent, string $filename, string $sectionName, string|null $path = null) {
			if ($file = $this->file($parent, $filename)) {
				return $this->sectionApi($file, $sectionName, $path);
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
							'sort' => $this->requestBody('sort')
						],
						'source'   => $source,
						'template' => $this->requestBody('template'),
						'filename' => $filename
					], move: true);
				},
				preflight: function (string $filename, string|null $template) use ($parent) {
					$file = new File([
						'parent'   => $parent,
						'filename' => $filename,
						'template' => $template
					]);

					if ($file->permissions()->can('create') !== true) {
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

			if ($this->requestMethod() === 'GET') {
				return $files->search($this->requestQuery('q'));
			}

			return $files->query(array_filter([
				'limit'    => $this->requestBody('limit'),
				'offset'   => $this->requestBody('offset'),
				'paginate' => $this->requestBody('paginate'),
				'search'   => $this->requestBody('search'),
			], fn ($value) => $value !== null));
		}
	],
	[
		'pattern' => $parentPattern . '/sort',
		'method'  => 'PATCH',
		'action'  => function (string $path) {
			return $this->files($path)->changeSort(
				$this->requestBody('files'),
				$this->requestBody('index')
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
				$this->requestBody(),
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
					if ($file->permissions()->can('replace') !== true) {
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
			return $this->file($path, $filename)->changeName($this->requestBody('name'));
		}
	],
	[
		'pattern' => $parentPattern . '/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			$files = $this
				->site()
				->index(true)
				->filter('isListable', true)
				->files()
				->filter('isListable', true);

			if ($this->requestMethod() === 'GET') {
				return $files->search($this->requestQuery('q'));
			}

			return $files->query(array_filter([
				'limit'    => $this->requestBody('limit'),
				'offset'   => $this->requestBody('offset'),
				'paginate' => $this->requestBody('paginate'),
				'search'   => $this->requestBody('search'),
			], fn ($value) => $value !== null));
		}
	],
];
