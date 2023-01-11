<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Exception\LogicException;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The Dropdown response class handles Fiber
 * requests to render the JSON object for
 * dropdown menus
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Dropdown extends Json
{
	protected static string $key = '$dropdown';

	/**
	 * Returns the options for the changes dropdown
	 */
	public static function changes(): array
	{
		$kirby     = App::instance();
		$multilang = $kirby->multilang();
		$ids       = Str::split($kirby->request()->get('ids'));
		$options   = [];

		foreach ($ids as $id) {
			try {
				// parse the given ID to extract
				// the path and an optional query
				$uri    = new Uri($id);
				$path   = $uri->path()->toString();
				$query  = $uri->query();
				$option = Find::parent($path)->panel()->dropdownOption();

				// add the language to each option, if it is included in the query
				// of the given ID and the language actually exists
				if ($multilang && $query->language && $language = $kirby->language($query->language)) {
					$option['text'] .= ' (' . $language->code() . ')';
					$option['link']  .= '?language=' . $language->code();
				}

				$options[] = $option;
			} catch (Throwable) {
				continue;
			}
		}

		// the given set of ids does not match any
		// real models. This means that the stored ids
		// in local storage are not correct and the changes
		// store needs to be cleared
		if (empty($options) === true) {
			throw new LogicException('No changes for given models');
		}

		return $options;
	}

	/**
	 * Renders dropdowns
	 */
	public static function response($data, array $options = []): Response
	{
		if (is_array($data) === true) {
			$data = [
				'options' => array_values($data)
			];
		}

		return parent::response($data, $options);
	}
}
