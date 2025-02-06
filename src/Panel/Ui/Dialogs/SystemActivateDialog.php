<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SystemActivateDialog extends FormDialog
{
	public function __construct()
	{

		parent::__construct(
			fields: $this->fields(),
			submitButton: [
				'icon'  => 'key',
				'text'  => I18n::translate('activate'),
				'theme' => 'love',
			],
			value: [
				'license' => null,
				'email'   => null
			]
		);
	}

	public function fields(): array
	{
		$system = App::instance()->system();
		$local  = $system->isLocal();

		return [
			'domain' => [
				'label' => I18n::translate('license.activate.label'),
				'type'  => 'info',
				'theme' => $local ? 'warning' : 'info',
				'text'  => I18n::template('license.activate.' . ($local ? 'local' : 'domain'), ['host' => $system->indexUrl()])
			],
			'license' => [
				'label'       => I18n::translate('license.code.label'),
				'type'        => 'text',
				'required'    => true,
				'counter'     => false,
				'placeholder' => 'K-',
				'help'        => I18n::translate('license.code.help') . ' ' . '<a href="https://getkirby.com/buy" target="_blank">' . I18n::translate('license.buy') . ' &rarr;</a>'
			],
			'email' => Field::email(['required' => true])
		];
	}

	public function submit(): array
	{
		// @codeCoverageIgnoreStart
		$this->kirby->system()->register(
			$this->request->get('license'),
			$this->request->get('email')
		);

		return [
			'event'   => 'system.register',
			'message' => I18n::translate('license.success')
		];
		// @codeCoverageIgnoreEnd
	}
}
