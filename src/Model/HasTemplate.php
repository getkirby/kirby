<?php

namespace Kirby\Model;

trait HasTemplate
{
	public function changeTemplate(string $template): static
	{
		$this->meta = $this->storage()->changeMeta([
			'template' => $template,
		]);

		return $this;
	}

	public function intendedTemplate(): string
	{
		return $this->meta->template;
	}

	public function template(): string
	{
		return $this->meta->template;
	}
}
