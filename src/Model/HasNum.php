<?php

namespace Kirby\Model;

trait HasNum
{
	public function changeNum(int|null $num = null): static
	{
		$this->meta = $this->storage()->changeMeta([
			'num' => $num,
		]);

		return $this;
	}

	public function num(): int|null
	{
		return $this->meta->num;
	}
}
