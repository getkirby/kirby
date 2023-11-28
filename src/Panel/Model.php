<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Form\Form;
use Kirby\Http\Uri;
use Kirby\Toolkit\A;

/**
 * Provides information about the model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Model
{
	public function __construct(
		protected ModelWithContent $model
	) {
	}

	/**
	 * Get the content values for the model
	 */
	public function content(): array
	{
		return Form::for($this->model)->values();
	}

	/**
	 * Returns the drag text from a custom callback
	 * if the callback is defined in the config
	 * @internal
	 *
	 * @param string $type markdown or kirbytext
	 */
	public function dragTextFromCallback(string $type, ...$args): string|null
	{
		$option   = 'panel.' . $type . '.' . $this->model::CLASS_ALIAS . 'DragText';
		$callback = $this->model->kirby()->option($option);

		if ($callback instanceof Closure) {
			return $callback($this->model, ...$args);
		}

		return null;
	}

	/**
	 * Returns the correct drag text type
	 * depending on the given type or the
	 * configuration
	 *
	 * @internal
	 *
	 * @param string|null $type (`auto`|`kirbytext`|`markdown`)
	 */
	public function dragTextType(string|null $type = null): string
	{
		$type ??= 'auto';

		if ($type === 'auto') {
			$kirby = $this->model->kirby();
			$type  = $kirby->option('panel.kirbytext', true) ? 'kirbytext' : 'markdown';
		}

		return $type === 'markdown' ? 'markdown' : 'kirbytext';
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 */
	public function dropdownOption(): array
	{
		return [
			'icon'  => 'page',
			'image' => $this->image(['back' => 'black']),
			'link'  => $this->url(true),
			'text'  => $this->model->id(),
		];
	}

	/**
	 * Returns the Panel image definition
	 * @internal
	 */
	public function image(
		string|array|false|null $settings = [],
		string $layout = 'list'
	): array|null {
		// completely switched off
		if ($settings === false) {
			return null;
		}

		// skip image thumbnail if option
		// is explicitly set to show the icon
		if ($settings === 'icon') {
			$settings = ['query' => false];
		} elseif (is_string($settings) === true) {
			// convert string settings to proper array
			$settings = ['query' => $settings];
		}

		// merge with defaults and blueprint option
		$settings = array_merge(
			$this->imageDefaults(),
			$settings ?? [],
			$this->model->blueprint()->image() ?? [],
		);

		if ($image = $this->imageSource($settings['query'] ?? null)) {
			// main url
			$settings['url'] = $image->url();

			if ($image->isResizable() === true) {
				// only create srcsets for resizable files
				$settings['src']    = static::imagePlaceholder();
				$settings['srcset'] = $this->imageSrcset($image, $layout, $settings);
			} elseif ($image->isViewable() === true) {
				$settings['src'] = $image->url();
			}
		}

		unset($settings['query']);

		// resolve remaining options defined as query
		return A::map($settings, function ($option) {
			if (is_string($option) === false) {
				return $option;
			}

			return $this->model->toString($option);
		});
	}

	/**
	 * Default settings for Panel image
	 */
	protected function imageDefaults(): array
	{
		return [
			'back'  => 'pattern',
			'color' => 'gray-500',
			'cover' => false,
			'icon'  => 'page'
		];
	}

	/**
	 * Data URI placeholder string for Panel image
	 * @internal
	 */
	public static function imagePlaceholder(): string
	{
		return 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw';
	}

	/**
	 * Returns the image file object based on provided query
	 * @internal
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		$image = $this->model->query($query ?? null);

		// validate the query result
		if (
			$image instanceof CmsFile ||
			$image instanceof Asset
		) {
			return $image;
		}

		return null;
	}

	/**
	 * Provides the correct srcset string based on
	 * the layout and settings
	 * @internal
	 */
	protected function imageSrcset(
		CmsFile|Asset $image,
		string $layout,
		array $settings
	): string|null {
		// depending on layout type, set different sizes
		// to have multiple options for the srcset attribute
		$sizes = match ($layout) {
			'cards'    => [352, 864, 1408],
			'cardlets' => [96, 192],
			default    => [38, 76]
		};

		// no additional modfications needed if `cover: false`
		if (($settings['cover'] ?? false) === false) {
			return $image->srcset($sizes);
		}

		// for card layouts with `cover: true` provide
		// crops based on the card ratio
		if ($layout === 'cards') {
			$ratio = explode('/', $settings['ratio'] ?? '1/1');
			$ratio = $ratio[0] / $ratio[1];

			return $image->srcset([
				$sizes[0] . 'w' => [
					'width'  => $sizes[0],
					'height' => round($sizes[0] / $ratio),
					'crop'   => true
				],
				$sizes[1] . 'w' => [
					'width'  => $sizes[1],
					'height' => round($sizes[1] / $ratio),
					'crop'   => true
				],
				$sizes[2] . 'w' => [
					'width'  => $sizes[2],
					'height' => round($sizes[2] / $ratio),
					'crop'   => true
				]
			]);
		}

		// for list and cardlets with `cover: true`
		// provide square crops in two resolutions
		return $image->srcset([
			'1x' => [
				'width'  => $sizes[0],
				'height' => $sizes[0],
				'crop'   => true
			],
			'2x' => [
				'width'  => $sizes[1],
				'height' => $sizes[1],
				'crop'   => true
			]
		]);
	}

	/**
	 * Checks for disabled dropdown options according
	 * to the given permissions
	 */
	public function isDisabledDropdownOption(
		string $action,
		array $options,
		array $permissions
	): bool {
		$option = $options[$action] ?? true;

		return
			$permissions[$action] === false ||
			$option === false ||
			$option === 'false';
	}

	/**
	 * Returns lock info for the Panel
	 *
	 * @return array|false array with lock info,
	 *                     false if locking is not supported
	 */
	public function lock(): array|false
	{
		return $this->model->lock()?->toArray() ?? false;
	}

	/**
	 * Returns an array of all actions
	 * that can be performed in the Panel
	 * This also checks for the lock status
	 *
	 * @param array $unlock An array of options that will be force-unlocked
	 */
	public function options(array $unlock = []): array
	{
		$options = $this->model->permissions()->toArray();

		if ($this->model->isLocked()) {
			foreach ($options as $key => $value) {
				if (in_array($key, $unlock)) {
					continue;
				}

				$options[$key] = false;
			}
		}

		return $options;
	}

	/**
	 * Returns the full path without leading slash
	 */
	abstract public function path(): string;

	/**
	 * Prepares the response data for page pickers
	 * and page fields
	 */
	public function pickerData(array $params = []): array
	{
		return [
			'id'       => $this->model->id(),
			'image'    => $this->image(
				$params['image'] ?? [],
				$params['layout'] ?? 'list'
			),
			'info'     => $this->model->toSafeString($params['info'] ?? false),
			'link'     => $this->url(true),
			'sortable' => true,
			'text'     => $this->model->toSafeString($params['text'] ?? false),
			'uuid'     => $this->model->uuid()?->toString() ?? $this->model->id(),
		];
	}

	/**
	 * Returns the data array for the
	 * view's component props
	 * @internal
	 */
	public function props(): array
	{
		$blueprint = $this->model->blueprint();
		$request   = $this->model->kirby()->request();
		$tabs      = $blueprint->tabs();
		$tab       = $blueprint->tab($request->get('tab')) ?? $tabs[0] ?? null;

		$props = [
			'lock'        => $this->lock(),
			'permissions' => $this->model->permissions()->toArray(),
			'tabs'        => $tabs,
		];

		// only send the tab if it exists
		// this will let the vue component define
		// a proper default value
		if ($tab) {
			$props['tab'] = $tab;
		}

		return $props;
	}

	/**
	 * Returns link url and title
	 * for model (e.g. used for prev/next navigation)
	 * @internal
	 */
	public function toLink(string $title = 'title'): array
	{
		return [
			'link'    => $this->url(true),
			'title'   => $title = (string)$this->model->{$title}()
		];
	}

	/**
	 * Returns link url and title
	 * for optional sibling model and
	 * preserves tab selection
	 *
	 * @internal
	 */
	protected function toPrevNextLink(
		ModelWithContent|null $model = null,
		string $title = 'title'
	): array|null {
		if ($model === null) {
			return null;
		}

		$data = $model->panel()->toLink($title);

		if ($tab = $model->kirby()->request()->get('tab')) {
			$uri = new Uri($data['link'], [
				'query' => ['tab' => $tab]
			]);

			$data['link'] = $uri->toString();
		}

		return $data;
	}

	/**
	 * Returns the url to the editing view
	 * in the Panel
	 *
	 * @internal
	 */
	public function url(bool $relative = false): string
	{
		if ($relative === true) {
			return '/' . $this->path();
		}

		return $this->model->kirby()->url('panel') . '/' . $this->path();
	}

	/**
	 * Returns the data array for
	 * this model's Panel view
	 *
	 * @internal
	 */
	abstract public function view(): array;
}
