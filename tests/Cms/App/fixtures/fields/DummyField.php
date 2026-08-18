<?php

namespace Kirby\Cms\Fixtures;

use Kirby\Form\Field\InputField;

class DummyField extends InputField
{
	protected mixed $value = null;

	public function __construct(
		protected string $homer = 'simpson',
		protected string $peter = 'pan'
	) {
		parent::__construct();
	}

	public function homer(): string
	{
		return $this->homer;
	}

	public function peter(): string
	{
		return $this->peter;
	}
}
