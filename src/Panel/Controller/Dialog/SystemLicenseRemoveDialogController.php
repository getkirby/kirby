<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Override;

/**
 * Dialog to remove the site's license
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @internal
 */
class SystemLicenseRemoveDialogController extends DialogController
{
	#[Override]
	public function load(): Dialog
	{
		return new RemoveDialog(
			text: $this->i18n('license.remove.text'),
			size: 'medium',
			submitButton: [
				'icon'  => 'trash',
				'text'  => $this->i18n('remove'),
				'theme' => 'negative',
			]
		);
	}

	/**
	 * @codeCoverageIgnore
	 */
	#[Override]
	public function submit(): bool
	{
		return $this->kirby->system()->license()->delete();
	}
}
