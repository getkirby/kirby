<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Locale;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The `$language` object represents
 * a single language in a multi-language
 * Kirby setup. You can, for example,
 * use the methods of this class to get
 * the name or locale of a language,
 * check for the default language,
 * get translation strings and many
 * more things.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Language extends Model
{
	/**
	 * @var string
	 */
	protected $code;

	/**
	 * @var bool
	 */
	protected $default;

	/**
	 * @var string
	 */
	protected $direction;

	/**
	 * @var array
	 */
	protected $locale;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array|null
	 */
	protected $slugs;

	/**
	 * @var array|null
	 */
	protected $smartypants;

	/**
	 * @var array|null
	 */
	protected $translations;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * Creates a new language object
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		$this->setRequiredProperties($props, [
			'code'
		]);

		$this->setOptionalProperties($props, [
			'default',
			'direction',
			'locale',
			'name',
			'slugs',
			'smartypants',
			'translations',
			'url',
		]);
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns the language code
	 * when the language is converted to a string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->code();
	}

	/**
	 * Returns the base Url for the language
	 * without the path or other cruft
	 *
	 * @return string
	 */
	public function baseUrl(): string
	{
		$kirbyUrl    = $this->kirby()->url();
		$languageUrl = $this->url();

		if (empty($this->url)) {
			return $kirbyUrl;
		}

		if (Str::startsWith($languageUrl, $kirbyUrl) === true) {
			return $kirbyUrl;
		}

		return Url::base($languageUrl) ?? $kirbyUrl;
	}

	/**
	 * Returns the language code/id.
	 * The language code is used in
	 * text file names as appendix.
	 *
	 * @return string
	 */
	public function code(): string
	{
		return $this->code;
	}

	/**
	 * Internal converter to create or remove
	 * translation files.
	 *
	 * @param string $from
	 * @param string $to
	 * @return bool
	 */
	protected static function converter(string $from, string $to): bool
	{
		$kirby = App::instance();
		$site  = $kirby->site();

		// convert site
		foreach ($site->files() as $file) {
			F::move($file->contentFile($from, true), $file->contentFile($to, true));
		}

		F::move($site->contentFile($from, true), $site->contentFile($to, true));

		// convert all pages
		foreach ($kirby->site()->index(true) as $page) {
			foreach ($page->files() as $file) {
				F::move($file->contentFile($from, true), $file->contentFile($to, true));
			}

			F::move($page->contentFile($from, true), $page->contentFile($to, true));
		}

		// convert all users
		foreach ($kirby->users() as $user) {
			foreach ($user->files() as $file) {
				F::move($file->contentFile($from, true), $file->contentFile($to, true));
			}

			F::move($user->contentFile($from, true), $user->contentFile($to, true));
		}

		return true;
	}

	/**
	 * Creates a new language object
	 *
	 * @internal
	 * @param array $props
	 * @return static
	 */
	public static function create(array $props)
	{
		$props['code'] = Str::slug($props['code'] ?? null);
		$kirby         = App::instance();
		$languages     = $kirby->languages();

		// make the first language the default language
		if ($languages->count() === 0) {
			$props['default'] = true;
		}

		$language = new static($props);

		// validate the new language
		LanguageRules::create($language);

		$language->save();

		if ($languages->count() === 0) {
			static::converter('', $language->code());
		}

		// update the main languages collection in the app instance
		App::instance()->languages(false)->append($language->code(), $language);

		return $language;
	}

	/**
	 * Delete the current language and
	 * all its translation files
	 *
	 * @internal
	 * @return bool
	 * @throws \Kirby\Exception\Exception
	 */
	public function delete(): bool
	{
		$kirby     = App::instance();
		$languages = $kirby->languages();
		$code      = $this->code();
		$isLast    = $languages->count() === 1;

		if (F::remove($this->root()) !== true) {
			throw new Exception('The language could not be deleted');
		}

		if ($isLast === true) {
			$this->converter($code, '');
		} else {
			$this->deleteContentFiles($code);
		}

		// get the original language collection and remove the current language
		$kirby->languages(false)->remove($code);

		return true;
	}

	/**
	 * When the language is deleted, all content files with
	 * the language code must be removed as well.
	 *
	 * @param mixed $code
	 * @return bool
	 */
	protected function deleteContentFiles($code): bool
	{
		$kirby = App::instance();
		$site  = $kirby->site();

		F::remove($site->contentFile($code, true));

		foreach ($kirby->site()->index(true) as $page) {
			foreach ($page->files() as $file) {
				F::remove($file->contentFile($code, true));
			}

			F::remove($page->contentFile($code, true));
		}

		foreach ($kirby->users() as $user) {
			foreach ($user->files() as $file) {
				F::remove($file->contentFile($code, true));
			}

			F::remove($user->contentFile($code, true));
		}

		return true;
	}

	/**
	 * Reading direction of this language
	 *
	 * @return string
	 */
	public function direction(): string
	{
		return $this->direction;
	}

	/**
	 * Check if the language file exists
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return file_exists($this->root());
	}

	/**
	 * Checks if this is the default language
	 * for the site.
	 *
	 * @return bool
	 */
	public function isDefault(): bool
	{
		return $this->default;
	}

	/**
	 * The id is required for collections
	 * to work properly. The code is used as id
	 *
	 * @return string
	 */
	public function id(): string
	{
		return $this->code;
	}

	/**
	 * Loads the language rules for provided locale code
	 *
	 * @param string $code
	 */
	public static function loadRules(string $code)
	{
		$kirby = App::instance();
		$code  = Str::contains($code, '.') ? Str::before($code, '.') : $code;
		$file  = $kirby->root('i18n:rules') . '/' . $code . '.json';

		if (F::exists($file) === false) {
			$file = $kirby->root('i18n:rules') . '/' . Str::before($code, '_') . '.json';
		}

		try {
			return Data::read($file);
		} catch (\Exception $e) {
			return [];
		}
	}

	/**
	 * Returns the PHP locale setting array
	 *
	 * @param int $category If passed, returns the locale for the specified category (e.g. LC_ALL) as string
	 * @return array|string
	 */
	public function locale(int $category = null)
	{
		if ($category !== null) {
			return $this->locale[$category] ?? $this->locale[LC_ALL] ?? null;
		} else {
			return $this->locale;
		}
	}

	/**
	 * Returns the human-readable name
	 * of the language
	 *
	 * @return string
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Returns the URL path for the language
	 *
	 * @return string
	 */
	public function path(): string
	{
		if ($this->url === null) {
			return $this->code;
		}

		return Url::path($this->url);
	}

	/**
	 * Returns the routing pattern for the language
	 *
	 * @return string
	 */
	public function pattern(): string
	{
		$path = $this->path();

		if (empty($path) === true) {
			return '(:all)';
		}

		return $path . '/(:all?)';
	}

	/**
	 * Returns the absolute path to the language file
	 *
	 * @return string
	 */
	public function root(): string
	{
		return App::instance()->root('languages') . '/' . $this->code() . '.php';
	}

	/**
	 * Returns the LanguageRouter instance
	 * which is used to handle language specific
	 * routes.
	 *
	 * @return \Kirby\Cms\LanguageRouter
	 */
	public function router()
	{
		return new LanguageRouter($this);
	}

	/**
	 * Get slug rules for language
	 *
	 * @internal
	 * @return array
	 */
	public function rules(): array
	{
		$code = $this->locale(LC_CTYPE);
		$data = static::loadRules($code);
		return array_merge($data, $this->slugs());
	}

	/**
	 * Saves the language settings in the languages folder
	 *
	 * @internal
	 * @return $this
	 */
	public function save()
	{
		try {
			$existingData = Data::read($this->root());
		} catch (Throwable $e) {
			$existingData = [];
		}

		$props = [
			'code'         => $this->code(),
			'default'      => $this->isDefault(),
			'direction'    => $this->direction(),
			'locale'       => Locale::export($this->locale()),
			'name'         => $this->name(),
			'translations' => $this->translations(),
			'url'          => $this->url,
		];

		$data = array_merge($existingData, $props);

		ksort($data);

		Data::write($this->root(), $data);

		return $this;
	}

	/**
	 * @param string $code
	 * @return $this
	 */
	protected function setCode(string $code)
	{
		$this->code = trim($code);
		return $this;
	}

	/**
	 * @param bool $default
	 * @return $this
	 */
	protected function setDefault(bool $default = false)
	{
		$this->default = $default;
		return $this;
	}

	/**
	 * @param string $direction
	 * @return $this
	 */
	protected function setDirection(string $direction = 'ltr')
	{
		$this->direction = $direction === 'rtl' ? 'rtl' : 'ltr';
		return $this;
	}

	/**
	 * @param string|array $locale
	 * @return $this
	 */
	protected function setLocale($locale = null)
	{
		if ($locale === null) {
			$this->locale = [LC_ALL => $this->code];
		} else {
			$this->locale = Locale::normalize($locale);
		}

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	protected function setName(string $name = null)
	{
		$this->name = trim($name ?? $this->code);
		return $this;
	}

	/**
	 * @param array $slugs
	 * @return $this
	 */
	protected function setSlugs(array $slugs = null)
	{
		$this->slugs = $slugs ?? [];
		return $this;
	}

	/**
	 * @param array $smartypants
	 * @return $this
	 */
	protected function setSmartypants(array $smartypants = null)
	{
		$this->smartypants = $smartypants ?? [];
		return $this;
	}

	/**
	 * @param array $translations
	 * @return $this
	 */
	protected function setTranslations(array $translations = null)
	{
		$this->translations = $translations ?? [];
		return $this;
	}

	/**
	 * @param string $url
	 * @return $this
	 */
	protected function setUrl(string $url = null)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Returns the custom slug rules for this language
	 *
	 * @return array
	 */
	public function slugs(): array
	{
		return $this->slugs;
	}

	/**
	 * Returns the custom SmartyPants options for this language
	 *
	 * @return array
	 */
	public function smartypants(): array
	{
		return $this->smartypants;
	}

	/**
	 * Returns the most important
	 * properties as array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'code'      => $this->code(),
			'default'   => $this->isDefault(),
			'direction' => $this->direction(),
			'locale'    => $this->locale(),
			'name'      => $this->name(),
			'rules'     => $this->rules(),
			'url'       => $this->url()
		];
	}

	/**
	 * Returns the translation strings for this language
	 *
	 * @return array
	 */
	public function translations(): array
	{
		return $this->translations;
	}

	/**
	 * Returns the absolute Url for the language
	 *
	 * @return string
	 */
	public function url(): string
	{
		$url = $this->url;

		if ($url === null) {
			$url = '/' . $this->code;
		}

		return Url::makeAbsolute($url, $this->kirby()->url());
	}

	/**
	 * Update language properties and save them
	 *
	 * @internal
	 * @param array $props
	 * @return static
	 */
	public function update(array $props = null)
	{
		// don't change the language code
		unset($props['code']);

		// make sure the slug is nice and clean
		$props['slug'] = Str::slug($props['slug'] ?? null);

		$kirby   = App::instance();
		$updated = $this->clone($props);

		// validate the updated language
		LanguageRules::update($updated);

		// convert the current default to a non-default language
		if ($updated->isDefault() === true) {
			if ($oldDefault = $kirby->defaultLanguage()) {
				$oldDefault->clone(['default' => false])->save();
			}

			$code = $this->code();
			$site = $kirby->site();

			touch($site->contentFile($code));

			foreach ($kirby->site()->index(true) as $page) {
				$files = $page->files();

				foreach ($files as $file) {
					touch($file->contentFile($code));
				}

				touch($page->contentFile($code));
			}
		} elseif ($this->isDefault() === true) {
			throw new PermissionException('Please select another language to be the primary language');
		}

		$language = $updated->save();

		// make sure the language is also updated in the Kirby language collection
		App::instance()->languages(false)->set($language->code(), $language);

		return $language;
	}
}
