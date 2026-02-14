<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\App;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\Str;

/**
 * View button to switch content translation languages
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class LanguagesButton extends ViewButton
{
	protected App $kirby;

	public function __construct(
		ModelWithContent $model
	) {
		$this->kirby = $model->kirby();

		parent::__construct(
			component: 'k-languages-dropdown',
			model: $model,
			class: 'k-languages-dropdown',
			icon: 'translate',
			// Panel dropdown endpoint to load options
			// only when dropdown is opened
			options: $model->panel()->url(true) . '/languages',
			responsive: 'text',
			text: Str::upper($this->kirby->language()?->code())
		);
	}

	/**
	 * Returns if any translation other than the current one has unsaved changes
	 * (the current language has to be handled in `k-languages-dropdown` as its
	 * state can change dynamically without another backend request)
	 */
	public function hasDiff(): bool
	{
		foreach (Languages::ensure() as $language) {
			if ($this->kirby->language()?->code() !== $language->code()) {
				if ($this->model->version('changes')->exists($language) === true) {
					return true;
				}
			}
		}

		return false;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'hasDiff' => $this->hasDiff()
		];
	}

	public function render(): array|null
	{
		// hides the language selector when there are less than 2 languages
		if ($this->kirby->languages()->count() < 2) {
			return null;
		}

		return parent::render();
	}
}
