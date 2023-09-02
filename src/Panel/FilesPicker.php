<?php

namespace Kirby\Panel;

use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;

/**
 * The FilesPicker class helps to fetch the right files
 * for for the files picker dialog in the Panel.
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FilesPicker extends ModelsPicker
{
	/**
	 * Extends the basic defaults
	 */
	public function defaults(): array
	{
		$defaults = parent::defaults();
		$defaults['text'] = '{{ file.filename }}';

		return $defaults;
	}

	/**
	 * Search all files for the picker
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function items(): Files|null
	{
		$model = $this->options['model'];

		// find the right default query
		$query   = $this->options['query'];
		$query ??= match (true) {
			$model instanceof File => 'file.siblings',
			default                => $model::CLASS_ALIAS . '.files'
		};

		// fetch all files for the picker
		$files = $model->query($query);

		// help mitigate some typical query usage issues
		// by converting site and page objects to proper
		// pages by returning their children
		$files = match (true) {
			$files instanceof Site,
			$files instanceof Page,
			$files instanceof User  => $files->files(),
			$files instanceof Files => $files,

			default => throw new InvalidArgumentException('Your query must return a set of files')
		};

		// filter protected and hidden pages
		$files = $files->filter('isListable', true);

		// search
		$files = $this->search($files);

		// paginate
		return $this->paginate($files);
	}
}
