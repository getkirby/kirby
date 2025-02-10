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
		ModelWithContent $model
	) {
		$this->kirby = $model->kirby();

		parent::__construct(
			component: 'k-languages-dropdown',
			model: $model,
			class: 'k-languages-dropdown',
			icon: 'translate',
			// Fiber dropdown endpoint to load options
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
	public function hasChanges(): bool
	{
		foreach (Languages::ensure() as $language) {
			if ($this->kirby->language()?->code() !== $language->code()) {
				if ($this->model->version(VersionId::changes())->exists($language) === true) {
					return true;
				}
			}
		}

		return false;
	}

	public function option(Language $language): array
	{
		$changes = $this->model->version('changes');

		return [
			'text'    => $language->name(),
			'code'    => $language->code(),
			'current' => $language->code() === $this->kirby->language()?->code(),
			'default' => $language->isDefault(),
			'changes' => $changes->exists($language),
			'lock'    => $changes->isLocked('*')
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
			'hasChanges' => $this->hasChanges()
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
