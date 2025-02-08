<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\Language;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Dialog to delete a language
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguageDeleteDialog extends RemoveDialog
{
	use IsForLanguage;

	public function __construct(
		public Language $language
	) {
		parent::__construct(
			text: I18n::template('language.delete.confirm', [
				'name' => Escape::html($this->language->name())
			])
		);
	}

	public function submit(): array
	{
		$this->language->delete();

		return [
			'event'    => 'language.delete',
			'redirect' => 'languages'
		];
	}
}
