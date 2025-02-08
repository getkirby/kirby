<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Cms\License;
use Kirby\Exception\LogicException;
use Kirby\Panel\Ui\Dialog;
use Kirby\Toolkit\I18n;

/**
 * Dialog to display (and potentially renew) the license
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SystemLicenseDialog extends Dialog
{
	protected License $license;

	public function __construct(
		License|null $license = null
	) {
		$this->license = $license ?? App::instance()->system()->license();

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
		return $this->license->status()->renewable();
	}

	public function license(): array
	{
		$status     = $this->license->status();
		$obfuscated = $this->kirby->user()->isAdmin() === false;

		return [
			'code'  => $this->license->code($obfuscated),
			'icon'  => $status->icon(),
			'info'  => $status->info($this->license->renewal('Y-m-d', 'date')),
			'theme' => $status->theme(),
			'type'  => $this->license->label(),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'license' => $this->license()
		];
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function submit(): array
	{
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
	}
}
