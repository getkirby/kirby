<?php

namespace Kirby\Block;

use Kirby\Content\Field;
use Kirby\Cms\Files;

/**
 * Represents a gallery block
 * @since 4.1.0
 *
 * @package   Kirby Block
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class GalleryBlock extends Block
{
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
			'attrs'   => $this->attrs(),
			'caption' => $this->caption(),
			'crop'    => $this->crop(),
			'files'   => $this->files(),
			'ratio'   => $this->ratio(),
		];
	}

	public function files(): Files
	{
		return $this->content()->images()->toFiles();
	}

	public function ratio(): Field
	{
		return $this->content()->ratio()->or('auto');
	}
}
