<?php

namespace Kirby\Model;

trait HasTimestamps
{
	public function created(): int
	{
		return $this->meta->created;
	}

	public function modified(): int
	{
		return $this->meta->modified;
	}
}
