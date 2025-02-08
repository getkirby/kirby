<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Toolkit\I18n;

/**
 * Dialog to create a new language
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguageCreateDialog extends FormDialog
{
	public function __construct(
		string|array|false|null $submitButton = null
	) {
		parent::__construct(
			component: 'k-language-dialog',
			fields: $this->fields(),
			submitButton: $submitButton ?? I18n::translate('language.create'),
			value: $this->value()
		);
	}

	public function fields(): array
	{
		return [
			'name' => [
				'counter'  => false,
				'label'    => I18n::translate('language.name'),
				'type'     => 'text',
				'required' => true,
				'icon'     => 'title'
			],
			'code' => [
				'label'    => I18n::translate('language.code'),
				'type'     => 'text',
				'required' => true,
				'counter'  => false,
				'icon'     => 'translate',
				'width'    => '1/2'
			],
			'direction' => [
				'label'    => I18n::translate('language.direction'),
				'type'     => 'select',
				'required' => true,
				'empty'    => false,
				'options'  => [
					[
						'value' => 'ltr',
						'text'  => I18n::translate('language.direction.ltr')
					],
					[
						'value' => 'rtl',
						'text' => I18n::translate('language.direction.rtl')
					]
				],
				'width'    => '1/2'
			],
			'locale' => [
				'counter' => false,
				'label'   => I18n::translate('language.locale'),
				'type'    => 'text',
			],
		];
	}

	public function submit(): array
	{
		$data = $this->request->get([
			'code',
			'direction',
			'locale',
			'name'
		]);
		$this->kirby->languages()->create($data);

		return [
			'event' => 'language.create'
		];
	}

	public function value(): array
	{
		return [
			'code'      => '',
			'direction' => 'ltr',
			'locale'    => '',
			'name'      => '',
		];
	}
}
