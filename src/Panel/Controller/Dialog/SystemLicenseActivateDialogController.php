<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Dialog to activate/register the site with a license
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @internal
 */
class SystemLicenseActivateDialogController extends DialogController
{
	public function fields(): array
	{
		$system = $this->kirby->system();
		$local  = $system->isLocal();

		return [
			'domain' => [
				'label' => $this->i18n('license.activate.label'),
				'type'  => 'info',
				'theme' => $local ? 'warning' : 'info',
				'text'  => $this->i18n('license.activate.' . ($local ? 'local' : 'domain'), ['host' => $system->indexUrl()])
			],
			'license' => [
				'label'       => $this->i18n('license.code.label'),
				'type'        => 'text',
				'required'    => true,
				'counter'     => false,
				'placeholder' => 'K-',
				'help'        => $this->i18n('license.code.help') . ' ' . '<a href="https://getkirby.com/buy" target="_blank">' . $this->i18n('license.buy') . ' &rarr;</a>'
			],
			'email' => Field::email(['required' => true])
		];
	}

	public function load(): Dialog
	{
		return new FormDialog(
			fields: $this->fields(),
			submitButton: [
				'icon'  => 'key',
				'text'  => $this->i18n('activate'),
				'theme' => 'love',
			],
			value: [
				'license' => null,
				'email'   => null
			]
		);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function submit(): array
	{
		$this->kirby->system()->register(
			$this->request->get('license'),
			$this->request->get('email')
		);

		return [
			'event'   => 'system.register',
			'message' => $this->i18n('license.success')
		];
	}
}
