<?php

namespace Kirby\Block;

use Kirby\Content\Field;
use Kirby\Cms\File;
use Kirby\Cms\Html;

/**
 * Represents a video block
 * @since 4.1.0
 *
 * @package   Kirby Block
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class VideoBlock extends Block
{
	protected Origin $origin;

	public function attrs(): array
	{
		if ($this->origin() === Origin::Kirby) {
			return array_filter([
				'autoplay' => $this->autoplay(),
				'controls' => $this->controls(),
				'loop'     => $this->loop(),
				'muted'    => $this->muted(),
				'poster'   => $this->poster()?->url(),
				'preload'  => $this->preload(),
			]);
		}

		return [];
	}

	public function autoplay(): bool
	{
		return $this->content()->autoplay()->toBool();
	}

	public function caption(): Field
	{
		return $this->content()->caption();
	}

	public function controller(): array
	{
		return [
			...parent::controller(),
			'attrs'   => $this->attrs(),
			'caption' => $this->caption(),
			'embed'   => $this->embed(),
			'src'     => $this->src(),
		];
	}

	public function controls(): bool
	{
		return $this->content()->controls()->toBool();
	}

	public function embed(): string
	{
		return Html::video($this->url(), [], $this->attrs());
	}

	public function file(): File|null
	{
		return $this->content()->video()->toFile();
	}

	public function location(): Field
	{
		return $this->content()->location()->or('web');
	}

	public function loop(): bool
	{
		return $this->content()->loop()->toBool();
	}

	public function muted(): bool
	{
		return $this->content()->muted()->toBool();
	}

	public function origin(): Origin
	{
		return $this->origin ??= Origin::from($this->location()->value());
	}

	public function poster(): File|null
	{
		return $this->content()->poster()->toFile();
	}

	public function preload(): bool
	{
		return $this->content()->preload()->toBool();
	}

	public function src(): Field|null
	{
		if ($this->origin() === Origin::Web) {
			return $this->content()->url();
		}

		return $this->content()->url()->value($this->file()?->url());
	}

	/**
	 * @deprecated Use `$block->src()` instead
	 */
	public function url(): Field|null
	{
		return $this->src();
	}

}
