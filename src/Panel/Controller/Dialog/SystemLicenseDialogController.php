<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\License;
use Kirby\Exception\LogicException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;

/**
 *  Dialog to display (and potentially renew) the license
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @internal
 */
class SystemLicenseDialogController extends DialogController
{
	protected License $license;

	public function __construct(
		License|null $license = null
	) {
		parent::__construct();
		$this->license = $license ?? $this->kirby->system()->license();
	}

	public function isRenewable(): bool
	{
		return $this->license->status()->renewable();
	}

	public function license(): array
	{
		$status     = $this->license->status();
		$obfuscated = $this->kirby->user()?->isAdmin() !== true;

		return [
			'code'   => $this->license->code($obfuscated),
			'domain' => $this->license->domain(),
			'icon'   => $status->icon(),
			'info'   => $status->info($this->license->renewal('Y-m-d', 'date')),
			'theme'  => $status->theme(),
			'type'   => $this->license->label(),
		];
	}

	public function load(): Dialog
	{
		return new Dialog(
			component: 'k-license-dialog',
			cancelButton: $this->isRenewable(),
			submitButton: $this->isRenewable() ? [
				'icon'  => 'refresh',
				'text'  => $this->i18n('renew'),
				'theme' => 'love',
			] : false,
			license: $this->license()
		);
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
				'message' => $this->i18n('license.success')
			];
		}

		throw new LogicException(message: 'The upgrade failed');
	}
}
