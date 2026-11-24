<?php

namespace Kirby\Cms\Fixtures;

use Kirby\Form\Field\InputField;
use Kirby\Reflection\Attributes\Prop;

class DummyField extends InputField
{
	#[Prop]
	protected string $homer = 'simpson';

	#[Prop]
	protected string $peter = 'pan';
	protected mixed $value = null;

	public function homer(): string
	{
		return $this->homer;
	}

	public function peter(): string
	{
		return $this->peter;
	}
}
