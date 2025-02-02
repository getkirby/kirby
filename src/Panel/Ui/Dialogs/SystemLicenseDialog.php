<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\LogicException;
use Kirby\Panel\Ui\Dialog;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SystemLicenseDialog extends Dialog
{
	public function __construct() {
		parent::__construct(
			component: 'k-license-dialog',
			cancelButton: $this->isRenewable(),
			submitButton: $this->isRenewable() ? [
				'icon'  => 'refresh',
				'text'  => I18n::translate('renew'),
				'theme' => 'love',
			] : false
		);
	}

	public function isRenewable(): bool
	{
		return $this->kirby->system()->license()->status()->renewable();
	}

	public function license(): array
	{
		$license    = $this->kirby->system()->license();
		$status     = $license->status();
		$obfuscated = $this->kirby->user()->isAdmin() === false;

		return [
				'code'  => $license->code($obfuscated),
				'icon'  => $status->icon(),
				'info'  => $status->info($license->renewal('Y-m-d', 'date')),
				'theme' => $status->theme(),
				'type'  => $license->label(),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'license' => $this->license()
		];
	}

	public function submit(): array
	{
		// @codeCoverageIgnoreStart
		$response = $this->kirby->system()->license()->upgrade();

		// the upgrade is still needed
		if ($response['status'] === 'upgrade') {
			return [
				'redirect' => $response['url']
			];
		}

		// the upgrade has already been completed
		if ($response['status'] === 'complete') {
			return [
				'event'   => 'system.renew',
				'message' => I18n::translate('license.success')
			];
		}

		throw new LogicException(message: 'The upgrade failed');
		// @codeCoverageIgnoreEnd
	}
}
