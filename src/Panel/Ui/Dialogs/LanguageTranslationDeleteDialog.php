<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

/**
 * Dialog to delete a language variable
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguageTranslationDeleteDialog extends RemoveDialog
{
	use IsForLanguageVariable;

	public function __construct(
		public LanguageVariable $variable
	) {
		if ($this->variable->exists() === false) {
			throw new NotFoundException(
				key: 'language.variable.notFound'
			);
		}

		parent::__construct(
			text: I18n::template('language.variable.delete.confirm', [
				'key' => Escape::html($this->variable->key())
			])
		);
	}

	public function submit(): bool
	{
		return $this->variable->delete();
	}
}
