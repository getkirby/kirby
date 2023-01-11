<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The FilePicker class helps to
 * fetch the right files for the API calls
 * for the file picker component in the panel.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FilePicker extends Picker
{
	/**
	 * Extends the basic defaults
	 *
	 * @return array
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
	 * @return \Kirby\Cms\Files|null
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function items()
	{
		$model = $this->options['model'];

		// find the right default query
		$query = match (true) {
			empty($this->options['query']) === false
				=> $this->options['query'],
			$model instanceof File
				=> 'file.siblings',
			default
			=> $model::CLASS_ALIAS . '.files'
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

		// search
		$files = $this->search($files);

		// paginate
		return $this->paginate($files);
	}
}
