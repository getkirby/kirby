<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Toolkit\Str;

/**
 * View button to switch content translation languages
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class LanguagesDropdown extends ViewButton
{
	protected App $kirby;

	public function __construct(
	) {
		$this->kirby = App::instance();

		parent::__construct(
			component: 'k-languages-dropdown',
			class: 'k-languages-dropdown',
			icon: 'translate',
			options: $this->options(),
			responsive: 'text',
			text: Str::upper($this->kirby->language()?->code())
		);
	}

	public function option(Language $language): array
	{
		return [
			'text'    => $language->name(),
			'code'    => $language->code(),
			'current' => $language->code() === $this->kirby->language()?->code(),
		];
	}

	public function options(): array
	{
		$languages = $this->kirby->languages();
		$options   = [];

		if ($this->kirby->multilang() === false) {
			return $options;
		}

		// add the primary/default language first
		if ($default = $languages->default()) {
			$options[] = $this->option($default);
			$options[] = '-';
			$languages = $languages->not($default);
		}

		// add all secondary languages after the separator
		foreach ($languages as $language) {
			$options[] = $this->option($language);
		}

		return $options;
	}

	public function render(): array|null
	{
		if ($this->kirby->multilang() === false) {
			return null;
		}

		return parent::render();
	}
}
