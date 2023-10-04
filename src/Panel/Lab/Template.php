<?php

namespace Kirby\Panel\Lab;

use Kirby\Template\Template as BaseTemplate;

class Template extends BaseTemplate
{
	public function __construct(
		public string $file
	) {
		parent::__construct(
			name: basename($this->file)
		);
	}

	public function file(): string|null
	{
		return $this->file;
	}
}
