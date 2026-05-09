<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\LicenseType;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;

/**
 * Dialog to activate/register the site with a license
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
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
				'label' => $this->i18n('domain'),
				'theme' => 'white',
				'type'  => 'info',
				'icon'  => 'info',
				'text'  => $this->i18n('license.activate.domain', [
					'host' => $system->indexUrl()
				]),
			],
			'type' => [
				'label'    =>  $this->i18n('license.activate.label'),
				'type'     => 'toggles',
				'required' => true,
				'labels'   => true,
				'grow'     => true,
				'options'  => [
					[
						'icon'  => 'globe',
						'text'  =>  $this->i18n('license.regular.label'),
						'value' => 'regular'
					],
					[
						'icon'  => 'key',
						'text'  =>  $this->i18n('license.free.label'),
						'value' => 'free'
					]
				],
			],
			'warning' => [
				'type'  => 'info',
				'theme' => 'warning',
				'text'  =>  $this->i18n('license.activate.' . ($local ? 'local' : 'public'), [
					'host' => $system->indexUrl()
				]),
				'when'  => ['type' => $local ? 'regular' : 'free'],
			],
			'acknowledge' => [
				'when'     => ['type' => 'free'],
				'label'    =>  $this->i18n('license.activate.acknowledge.label'),
				'type'     => 'toggle',
				'text'     =>  $this->i18n('license.activate.acknowledge.text'),
				'required' => true,
				'help'     => $this->i18n('license.activate.acknowledge.help', [
					'url' => 'https://getkirby.com/license/free-licenses'
				]),
			],
			'license' => [
				'when'        => ['type' => 'regular'],
				'label'       => $this->i18n('license.code.label'),
				'type'        => 'text',
				'required'    => true,
				'counter'     => false,
				'placeholder' => 'K-',
				'help'        => $this->i18n('license.code.help') . ' ' . '<a href="https://getkirby.com/buy" target="_blank">' . $this->i18n('license.buy') . ' &rarr;</a>'
			],
			'email' => Field::email([
				'when'     => ['type' => 'regular'],
				'required' => true
			])
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
				'email'   => null,
				'type'    => 'regular'
			]
		);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function submit(): array
	{
		$type    = $this->request->get('type', 'regular');
		$email   = $this->request->get('email');
		$license = match ($type) {
			'free'  => LicenseType::Free->prefix(),
			default => $this->request->get('license')
		};

		$this->kirby->system()->register($license, $email);

		return [
			'event'   => 'system.register',
			'message' => match ($type) {
				'free'  => $this->i18n('license.success.free'),
				default => $this->i18n('license.success')
			}
		];
	}
}
