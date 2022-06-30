<?php

// routing pattern to match all models with files
$pattern = '(account|pages/[^/]+|site|users/[^/]+)';

/**
 * Files Routes
 */
return [

	[
		'pattern' => $pattern . '/files/(:any)/sections/(:any)',
		'method'  => 'GET',
		'action'  => function (string $path, string $filename, string $sectionName) {
			if ($section = $this->file($path, $filename)->blueprint()->section($sectionName)) {
				return $section->toResponse();
			}
		}
	],
	[
		'pattern' => $pattern . '/files/(:any)/fields/(:any)/(:all?)',
		'method'  => 'ALL',
		'action'  => function (string $parent, string $filename, string $fieldName, string $path = null) {
			if ($file = $this->file($parent, $filename)) {
				return $this->fieldApi($file, $fieldName, $path);
			}
		}
	],
	[
		'pattern' => $pattern . '/files',
		'method'  => 'GET',
		'action'  => function (string $path) {
			return $this->parent($path)->files()->sorted();
		}
	],
	[
		'pattern' => $pattern . '/files',
		'method'  => 'POST',
		'action'  => function (string $path) {
			// move_uploaded_file() not working with unit test
			// @codeCoverageIgnoreStart
			return $this->upload(function ($source, $filename) use ($path) {
				return $this->parent($path)->createFile([
					'content' => [
						'sort' => $this->requestBody('sort')
					],
					'source'   => $source,
					'template' => $this->requestBody('template'),
					'filename' => $filename
				]);
			});
			// @codeCoverageIgnoreEnd
		}
	],
	[
		'pattern' => $pattern . '/files/search',
		'method'  => 'GET|POST',
		'action'  => function (string $path) {
			$files = $this->parent($path)->files();

			if ($this->requestMethod() === 'GET') {
				return $files->search($this->requestQuery('q'));
			} else {
				return $files->query($this->requestBody());
			}
		}
	],
	[
		'pattern' => $pattern . '/files/sort',
		'method'  => 'PATCH',
		'action'  => function (string $path) {
			return $this->parent($path)->files()->changeSort(
				$this->requestBody('files'),
				$this->requestBody('index')
			);
		}
	],
	[
		'pattern' => $pattern . '/files/(:any)',
		'method'  => 'GET',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename);
		}
	],
	[
		'pattern' => $pattern . '/files/(:any)',
		'method'  => 'PATCH',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename)->update($this->requestBody(), $this->language(), true);
		}
	],
	[
		'pattern' => $pattern . '/files/(:any)',
		'method'  => 'POST',
		'action'  => function (string $path, string $filename) {
			return $this->upload(function ($source) use ($path, $filename) {
				return $this->file($path, $filename)->replace($source);
			});
		}
	],
	[
		'pattern' => $pattern . '/files/(:any)',
		'method'  => 'DELETE',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename)->delete();
		}
	],
	[
		'pattern' => $pattern . '/files/(:any)/name',
		'method'  => 'PATCH',
		'action'  => function (string $path, string $filename) {
			return $this->file($path, $filename)->changeName($this->requestBody('name'));
		}
	],
	[
		'pattern' => 'files/search',
		'method'  => 'GET|POST',
		'action'  => function () {
			$files = $this
				->site()
				->index(true)
				->filter('isReadable', true)
				->files();

			if ($this->requestMethod() === 'GET') {
				return $files->search($this->requestQuery('q'));
			} else {
				return $files->query($this->requestBody());
			}
		}
	],
];
