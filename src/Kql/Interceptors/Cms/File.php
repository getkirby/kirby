<?php

namespace Kirby\Kql\Interceptors\Cms;

class File extends Model
{
	public const CLASS_ALIAS = 'file';

	protected $toArray = [
		'extension',
		'filename',
		'height',
		'id',
		'mime',
		'niceSize',
		'template',
		'type',
		'url',
		'width'
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForModels(),
			$this->allowedMethodsForParents(),
			$this->allowedMethodsForSiblings(),
			[
				'blur',
				'bw',
				'crop',
				'dataUri',
				'dimensions',
				'exif',
				'extension',
				'filename',
				'files',
				'grayscale',
				'greyscale',
				'height',
				'html',
				'isPortrait',
				'isLandscape',
				'isSquare',
				'mime',
				'name',
				'niceSize',
				'orientation',
				'ratio',
				'resize',
				'size',
				'srcset',
				'template',
				'templateSiblings',
				'thumb',
				'type',
				'width'
			]
		);
	}

	public function dimensions(): array
	{
		return $this->object->dimensions()->toArray();
	}

	public function exif(): array
	{
		return $this->object->exif()->toArray();
	}
}
