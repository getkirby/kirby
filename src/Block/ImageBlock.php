<?php

namespace Kirby\Block;

use Kirby\Content\Field;
use Kirby\Cms\File;
use Kirby\Cms\Url;
use Kirby\Toolkit\Html;

/**
 * Represents an image block
 * @since 4.1.0
 *
 * @package   Kirby Block
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ImageBlock extends Block
{
	protected Origin $origin;

	public function alt(): Field
	{
		if ($this->origin() === Origin::Web) {
			return $this->content()->alt();
		}

		return $this->content()->alt()->or($this->file()?->alt());
	}

	public function attrs(): array
	{
		return [
			'data-crop'  => $this->crop()->toBool(),
			'data-ratio' => $this->ratio(),
		];
	}

	public function caption(): Field
	{
		return $this->content()->caption();
	}

	public function controller(): array
	{
		return [
			...parent::controller(),
			'alt'     => $this->alt(),
			'attrs'   => $this->attrs(),
			'caption' => $this->caption(),
			'crop'    => $this->crop(),
			'img'     => $this->img(),
			'link'    => $this->link(),
			'ratio'   => $this->ratio(),
			'src'     => $this->src(),
		];
	}

	public function file(): File|null
	{
		if ($this->origin() === Origin::Web) {
			return null;
		}

		return $this->content()->image()->toFile();
	}

	public function img(): string
	{
		return '<img src="' . $this->src()->esc() . '" alt="' . $this->alt()->esc() . '">';
	}

	public function link(): Field
	{
		return $this->content()->link()->value(function (string|null $value) {
			if (empty($value) === true) {
				return null;
			}

			return Url::to($value);
		});
	}

	public function location(): Field
	{
		return $this->content()->location()->or(Origin::Kirby->value);
	}

	public function origin(): Origin
	{
		return $this->origin ??= Origin::from($this->location()->value());
	}

	public function ratio(): Field
	{
		return $this->content()->ratio()->or('auto');
	}

	public function src(): Field
	{
		if ($this->origin() === Origin::Web) {
			return $this->content()->src();
		}

		return $this->content()->src()->value($this->file()?->url());
	}

}
