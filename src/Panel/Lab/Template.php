<?php

namespace Kirby\Panel\Lab;

use Kirby\Template\Template as BaseTemplate;

/**
 * Custom template class for lab examples
 *
 * @internal
 * @since 4.0.0
 * @codeCoverageIgnore
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Template extends BaseTemplate
{
	public function __construct(
		public string $file
	) {
		parent::__construct(
			name: basename($this->file)
		);
	}

	public function file(): string|null
	{
		return $this->file;
	}
}
