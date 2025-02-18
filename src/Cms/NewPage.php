<?php

namespace Kirby\Cms;

use Kirby\Content\ContentTranslation;
use Kirby\Content\MemoryStorage;
use Kirby\Content\Storage;
use Kirby\Content\Translation;
use Kirby\Content\Translations;
use Kirby\Content\VersionId;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

class NewPage extends Page
{
	public function clone(array $props = []): static
	{
		$clone = new static(array_replace_recursive($this->propertyData, $props));
		$class = get_class($this->storage());

		// Move the clone to a new instance of the same storage class
		// The storage classes might need to rely on the model instance
		// and thus we need to make sure that the cloned object is
		// passed on to the new storage instance
		$clone->moveToStorage(new $class($clone));

		return $clone;
	}

	public function content(string|null $languageCode = null): Content
	{
		// get the targeted language
		$language  = Language::ensure($languageCode ?? 'current');
		$versionId = VersionId::$render ?? VersionId::latest();
		$version   = $this->version($versionId);

		if ($version->exists($language) === true) {
			return $version->content($language);
		}

		return $this->version()->content($language);
	}

	public static function create(array $props): Page
	{
		$content  = $props['content'] ?? [];
		$template = $props['template'] ?? 'default';
		$model    = $props['model'] ?? $template;

		// create the instance with a limited set of props
		$page = static::factory($props = [
			...$props,
			'content'      => null,
			'isDraft'      => $props['isDraft'] ?? $props['draft'] ?? true,
			'model'        => $model,
			'slug'         => Url::slug($props['slug'] ?? $content['title'] ?? null),
			'template'     => $template,
			'translations' => null,
		]);

		// create the form to get the generate the defaults
		$form = Form::for($page, [
			'language' => Language::ensure('default')->code(),
		]);

		// merge the content back with the defaults
		$props['content'] = [
			...$form->strings(true),
			...$content,
		];

		// add a uuid if not already set
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] ??= Uuid::generate();
		}

		// keep the initial storage class
		$storage = get_class($page->storage());

		// keep the page in memory until it will be saved
		$page->moveToStorage(new MemoryStorage($page));

		// inject the content to make this page object usable in the hook
		$page = $page->save($props['content'], 'default');

		// run the hooks and creation action
		$page = $page->commit(
			'create',
			[
				'page'  => $page,
				'input' => $props
			],
			function ($page, $props) use ($storage) {
				// move to final storage
				$page->moveToStorage(new $storage($page));

				// flush the parent cache to get children and drafts right
				static::updateParentCollections($page, 'append');

				return $page;
			}
		);

		// publish the new page if a number is given
		if (isset($props['num']) === true) {
			$page = $page->changeStatus('listed', $props['num']);
		}

		return $page;
	}

	public static function factory($props): static
	{
		return static::model($props['model'] ?? $props['template'] ?? 'default', $props);
	}

	public static function model(string $name, array $props = []): static
	{
		$name    = strtolower($name);
		$class   = static::$models[$name] ?? null;
		$class ??= static::$models['default'] ?? null;

		if ($class !== null) {
			$object = new $class($props);

			if ($object instanceof self) {
				return $object;
			}
		}

		return new static($props);
	}

	public function moveToStorage(Storage $toStorage): static
	{
		$this->storage()->copyAll(to: $toStorage);
		$this->storage = $toStorage;
		return $this;
	}

	protected function setContent(array|null $content = null): static
	{
		if ($content === null) {
			return $this;
		}

		$this->moveToStorage(new MemoryStorage($this));
		$this->version()->save($content, 'default');

		return $this;
	}

	protected function setTemplate(string|null $template = null): static
	{
		if ($template !== null) {
			$this->intendedTemplate = $this->kirby()->template(strtolower($template));
		}

		return $this;
	}

	protected function setTranslations(array|null $translations = null): static
	{
		if ($translations === null) {
			return $this;
		}

		$this->moveToStorage(new MemoryStorage($this));

		Translations::create(
			model: $this,
			version: $this->version(),
			translations: $translations
		);

		return $this;
	}

	public function save(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// move the old model into memory
		$this->moveToStorage(new MemoryStorage($this));

		// update the clone
		$clone->version()->save($data ?? [], $languageCode ?? 'default', $overwrite);

		return $clone;
	}

	protected function saveContent(
		array|null $data = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveContent() is deprecated. Use $model->save() instead.');
		return $this->save($data, 'default', $overwrite);
	}

	protected function saveTranslation(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveTranslation() is deprecated. Use $model->save() instead.');
		return $this->save($data, $languageCode ?? 'default', $overwrite);
	}

	public function translation(
		string|null $languageCode = null
	): ContentTranslation|null {
		$language = Language::ensure($languageCode ?? 'current');

		return new Translation(
			model: $this,
			version: $this->version(),
			language: $language
		);
	}

	public function translations(): Translations
	{
		return Translations::load(
			model: $this,
			version: $this->version()
		);
	}
}
