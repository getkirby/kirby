<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\VersionId;
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
		protected ModelWithContent $model
	) {
		$this->kirby = $model->kirby();

		parent::__construct(
			component: 'k-languages-dropdown',
			class: 'k-languages-dropdown',
			icon: 'translate',
			// Fiber dropdown endpoint to load options
			// only when dropdown is opened
			options: $this->model->panel()->url(true) . '/languages',
			responsive: 'text',
			text: Str::upper($this->kirby->language()?->code())
		);
	}

	/**
	 * Returns the number of translations with unsaved changes
	 * other than the current one (as the current one will be considered
	 * dynamically in `<k-languages-dropdown>` based on its state)
	 */
	public function changes(): int
	{
		$count = 0;

		foreach (Languages::ensure() as $language) {
			if ($this->kirby->language()?->code() !== $language->code()) {
				if ($this->model->version(VersionId::changes())->exists($language) === true) {
					$count++;
				}
			}
		}

		return $count;
	}

	public function option(Language $language): array
	{
		return [
			'text'    => $language->name(),
			'code'    => $language->code(),
			'current' => $language->code() === $this->kirby->language()?->code(),
			'link'    => $this->model->panel()->url(true) . '?language=' . $language->code()
		];
	}

	/**
	 * Options are used in the Fiber dropdown routes
	 */
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

	public function props(): array
	{
		return [
			...parent::props(),
			'changes' => $this->changes()
		];
	}

	public function render(): array|null
	{
		if ($this->kirby->multilang() === false) {
			return null;
		}

		return parent::render();
	}
}
