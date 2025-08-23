<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\Version;
use Kirby\Panel\Controller\DropdownController;
use Override;

/**
 * Controls the dropdown for switching content translations
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class LanguagesDropdownController extends DropdownController
{
	protected Version $changes;

	public function __construct(
		public ModelWithContent $model
	) {
		parent::__construct();
		$this->changes = $this->model->version('changes');
	}

	public static function factory(
		string|null $parent = null,
		string|null $filename = null
	): static {
		if ($parent !== null && $filename !== null) {
			return new static(model: Find::file($parent, $filename));
		}

		if ($parent !== null) {
			return new static(model: Find::parent($parent));
		}

		return new static(model: App::instance()->site());
	}

	/**
	 * Options are used in the Fiber dropdown routes
	 */
	#[Override]
	public function load(): array
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

	public function option(Language $language): array
	{
		return [
			'text'    => $language->name(),
			'code'    => $language->code(),
			'current' => $language->code() === $this->kirby->language()?->code(),
			'default' => $language->isDefault(),
			'changes' => $this->changes->exists($language),
			'lock'    => $this->changes->isLocked('*')
		];
	}
}
