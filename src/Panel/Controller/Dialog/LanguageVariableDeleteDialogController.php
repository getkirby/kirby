<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\LanguageVariable;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Toolkit\Escape;

/**
 * Dialog controller for deleting a language variable
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class LanguageVariableDeleteDialogController extends DialogController
{
	public function __construct(
		public LanguageVariable $variable
	) {
	}

	public static function factory(string $language, string $key): static
	{
		$variable = Find::language($language)->variable($key, true);

		if ($variable->exists() === false) {
			throw new NotFoundException(key: 'language.variable.notFound');
		}

		return new static($variable);
	}

	public function load(): Dialog
	{
		return new RemoveDialog(
			text: $this->i18n('language.variable.delete.confirm', [
				'key' => Escape::html($this->variable->key())
			])
		);
	}

	public function submit(): bool
	{
		return $this->variable->delete();
	}
}
