<?php

namespace Kirby\Uuid;

use Kirby\Cms\Blocks;
use Kirby\Content\Field;
use Override;

/**
 * UUID for \Kirby\Cms\Block
 *
 * Not yet supported
 * @todo Finish for uuid-block-structure-support
 * @codeCoverageIgnore
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class BlockUuid extends FieldUuid
{
	protected const string TYPE  = 'block';
	protected const string FIELD = 'blocks';

	/**
	 * @var \Kirby\Cms\Block|null
	 */
	public Identifiable|null $model = null;

	/**
	 * Converts content field to a Blocks collection
	 * @unstable
	 */
	#[Override]
	public static function fieldToCollection(Field $field): Blocks
	{
		return $field->toBlocks();
	}
}
