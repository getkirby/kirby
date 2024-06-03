<?php

namespace Kirby\Content;

use Kirby\Cms\Collection;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;

/**
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Collection<\Kirby\Content\ContentTranslation>
 */
class ContentTranslations extends Collection
{
	public static function load(ModelWithContent $model): static
	{
		$translations = new static();

		foreach (Languages::ensure() as $language) {
			$translation = new ContentTranslation([
				'parent' => $model,
				'code'   => $language->code(),
			]);

			$translations->data[$translation->code()] = $translation;
		}

		return $translations;
	}
}
