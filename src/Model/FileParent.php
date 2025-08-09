<?php

namespace Kirby\Model;

class FileParent
{
	protected Model $parent;

	public function __construct(
		protected Model|string $pointer,
	) {
	}

	public static function from(Model|self|string $parent): static
	{
		if ($parent instanceof self) {
			return $parent;
		}

		return new static($parent);
	}

	public function load(): Model
	{
		return $this->parent ??= match (true) {
			$this->pointer === null
				=> null,
			$this->pointer instanceof Model
				=> $this->pointer,
			default
				=> Page::findByIdentifier($this->pointer),
		};
	}
}
