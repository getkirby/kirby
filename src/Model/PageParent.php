<?php

namespace Kirby\Model;

class PageParent
{
	protected Page|null $page;

	public function __construct(
		protected Page|string|null $pointer,
	) {
	}

	public static function from(Page|self|string|null $parent): static
	{
		if ($parent instanceof self) {
			return $parent;
		}

		return new static($parent);
	}

	public function load(): Page|null
	{
		return $this->page ??= match (true) {
			$this->pointer === null
				=> null,
			$this->pointer instanceof Page
				=> $this->pointer,
			default
				=> Page::findByIdentifier($this->pointer),
		};
	}
}
