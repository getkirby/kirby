<?php

namespace Kirby\Kql\Interceptors\Panel;

use Kirby\Kql\Interceptor;

class Model extends Interceptor
{
	public const CLASS_ALIAS = 'panel';

	public function allowedMethods(): array
	{
		return [
			'dragText',
			'image',
			'path',
			'url',
		];
	}

	public function toArray(): array
	{
		return [
			'dragText' => $this->dragText(),
			'image'    => $this->image(),
			'path'     => $this->path(),
			'url'      => $this->url(),
		];
	}
}
