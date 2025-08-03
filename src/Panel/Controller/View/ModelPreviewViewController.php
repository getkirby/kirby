<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\I18n;

/**
 * Controls the preview view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ModelPreviewViewController extends ViewController
{
	public function __construct(
		public Page|Site $model,
		public string $versionId
	) {
		parent::__construct();
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view(view: $this->id(), model: $this->model)->defaults($this->model::CLASS_ALIAS . '.versions', 'languages')
			->bind(['versionId' => $this->versionId]);
	}

	public static function factory(string $path, string $versionId): static
	{
		return new static(
			model:     Find::parent($path),
			versionId: $versionId
		);
	}

	public function id(): string
	{
		return $this->model::CLASS_ALIAS . '.preview';
	}

	public function load(): View
	{
		$props = $this->props();

		if ($props['src']['latest'] === null) {
			throw new PermissionException('The preview is not available');
		}

		return new View(...$props);
	}

	public function props(): array
	{
		$controller = match (true) {
			$this->model instanceof Page => PageViewController::class,
			$this->model instanceof Site => SiteViewController::class,
		};

		$props = (new $controller($this->model))->props();

		$title = match (true) {
			$this->model instanceof Page => $props['title'],
			$this->model instanceof Site => I18n::translate('view.site'),
		};

		return [
			...$props,
			'component' => 'k-preview-view',
			'back'      => $props['link'],
			'buttons'   => $this->buttons(),
			'id'        => $this->id(),
			'src'       => $this->src(),
			'title'     => $title . ' | ' . I18n::translate('preview'),
			'versionId' => $this->versionId,
		];
	}

	public function src(): array
	{
		return [
			'latest'  => $this->model->previewUrl('latest'),
			'changes' => $this->model->previewUrl('changes'),
		];
	}
}
