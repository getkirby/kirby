<?php

namespace Kirby\Uuid;

use Kirby\Cms\Blocks;
use Kirby\Content\Field;

/**
 * UUID for \Kirby\Cms\Block
 *
 * Not yet supported
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @todo Finish for uuid-block-structure-support
 * @codeCoverageIgnore
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
	public static function fieldToCollection(Field $field): Blocks
	{
		return $field->toBlocks();
	}
}
