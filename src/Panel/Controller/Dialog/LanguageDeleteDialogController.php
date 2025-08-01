<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Dialog controller for deleting a language
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class LanguageDeleteDialogController extends DialogController
{
	public function __construct(
		public Language $language
	) {
	}

	public static function factory(string $language): static
	{
		return new static(language: Find::language($language));
	}

	public function load(): Dialog
	{
		return new RemoveDialog(
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
