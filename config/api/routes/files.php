<?php

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
			return $this->upload(function ($source, $filename) use ($path) {
				// move the source file from the temp dir
				return $this->parent($path)->createFile([
					'content' => [
						'sort' => $this->requestBody('sort')
					],
					'source'   => $source,
					'template' => $this->requestBody('template'),
					'filename' => $filename
				], true);
			});
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

			return $files->query($this->requestBody());
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
			// move the source file from the temp dir
			return $this->upload(
				fn ($source) => $this->file($path, $filename)->replace($source, true)
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

			return $files->query($this->requestBody());
		}
	],
];
