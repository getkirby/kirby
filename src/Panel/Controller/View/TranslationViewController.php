<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Form\Form;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;

/**
 * Controls the page view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TranslationViewController extends ViewController
{
	public function __construct(
		public Page|Site|User $model,
	) {
		parent::__construct();
	}

	public static function factory(string $path): static
	{
		return new static(
			model: Find::parent($path),
		);
	}

	public function fields(): array
	{
		return $this->formA()->fields()->toProps();
	}

	public function formA(): Form
	{
		return Form::for(model: $this->model, language: 'default');
	}

	public function formB(): Form
	{
		return Form::for(model: $this->model, language: 'current');
	}

	public function load(): View
	{
		return new View(
			api: $link = $this->model->panel()->url(true),
			component: 'k-translate-view',
			fields: $this->fields(),
			id: $this->model->id(),
			link: $link,
			lock: $this->model->lock()->toArray(),
			permissions: $this->model->permissions()->toArray(),
			title: $this->model->title()->value(),
			translationA: $this->translationA(),
			translationB: $this->translationB(),
			versions: $this->versions()
		);
	}

	public function translationA(): array
	{
		return $this->formA()->toFormValues();
	}

	public function translationB(): array
	{
		return $this->formB()->toFormValues();
	}

	public function versions(): array
	{
		$language = Language::ensure('current');
		$fields   = $this->formB()->fields();

		$latestVersion  = $this->model->version('latest');
		$changesVersion = $this->model->version('changes');

		$latestContent  = $latestVersion->content($language)->toArray();
		$changesContent = $latestContent;

		if ($changesVersion->exists($language) === true) {
			$changesContent = $changesVersion->content($language)->toArray();
		}

		return [
			'latest'  => $fields->reset()->fill($latestContent)->toFormValues(),
			'changes' => $fields->reset()->fill($changesContent)->toFormValues()
		];
	}
}
