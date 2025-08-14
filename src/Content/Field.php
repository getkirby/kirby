<?php

namespace Kirby\Content;

use Closure;
use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\HasMethods;
use Kirby\Cms\Html;
use Kirby\Cms\Layouts;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Structure;
use Kirby\Cms\Url;
use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Image\QrCode;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Dom;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Kirby\Toolkit\Xml;
use Kirby\Uuid\Permalink;
use Stringable;
use Throwable;

/**
 * Every field in a Kirby content text file
 * is being converted into such a Field object.
 *
 * Field methods can be registered for those Field
 * objects, which can then be used to transform or
 * convert the field value. This enables our
 * daisy-chaining API for templates and other components
 *
 * ```php
 * // Page field example with lowercase conversion
 * $page->myField()->lower();
 * ```
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Field implements Stringable
{
	use HasMethods;

	/**
	 * Creates a new field object
	 *
	 * @param \Kirby\Cms\ModelWithContent|null $parent Parent object if available. This will be the page, site, user or file to which the content belongs
	 * @param string $key The field name
	 */
	public function __construct(
		protected ModelWithContent|null $parent,
		protected string $key,
		public mixed $value
	) {
	}

	/**
	 * Magic caller for field methods
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		$method = strtolower($method);

		if ($this->hasMethod($method) === true) {
			return $this->callMethod($method, [clone $this, ...$arguments]);
		}

		// TODO: throw deprecation, then exception
		// when unknown method is called
		return $this;
	}

	/**
	 * Simplifies the var_dump result
	 * @codeCoverageIgnore
	 *
	 * @see self::toArray()
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Makes it possible to simply echo
	 * or stringify the entire object
	 *
	 * @see self::toString()
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Applies the callback function to the field
	 * @since 3.4.0
	 */
	public function callback(Closure $callback): mixed
	{
		return $callback($this);
	}

	/**
	 * Escapes the field value to be safely used in HTML
	 * templates without the risk of XSS attacks
	 *
	 * @param string $context Location of output (`html`, `attr`, `js`, `css`, `url` or `xml`)
	 */
	public function escape(string $context = 'html'): static
	{
		return $this->value(fn ($value) => Str::esc($value ?? '', $context));
	}

	/**
	 * Creates an excerpt of the field value without html
	 * or any other formatting.
	 */
	public function excerpt(
		int $chars = 0,
		bool $strip = true,
		string $rep = ' …'
	): static {
		return $this->value(Str::excerpt(
			string: $this->kirbytext()->value(),
			chars:  $chars,
			strip:  $strip,
			rep:    $rep
		));
	}

	/**
	 * Checks if the field exists in the content data array
	 */
	public function exists(): bool
	{
		return $this->parent->content()->has($this->key);
	}

	/**
	 * Converts the field content to valid HTML
	 */
	public function html(): static
	{
		return $this->value(fn ($value) => Html::encode($value));
	}

	/**
	 * Strips all block-level HTML elements from the field value,
	 * it can be safely placed inside of other inline elements
	 * without the risk of breaking the HTML structure.
	 * @since 3.3.0
	 */
	public function inline(): static
	{
		// List of valid inline elements taken from:
		// https://developer.mozilla.org/de/docs/Web/HTML/Inline_elemente
		// Obsolete elements, script tags, image maps and form elements have
		// been excluded for safety reasons and as they are most likely not
		// needed in most cases.
		return $this->value(
			fn ($value) => strip_tags($value ?? '', Html::$inlineList)
		);
	}

	/**
	 * Checks if the field content is empty
	 */
	public function isEmpty(): bool
	{
		$value = $this->value;

		if (is_string($value) === true) {
			$value = trim($value);
		}

		return
			$value === null ||
			$value === '' ||
			$value === [] ||
			$value === '[]';
	}

	/**
	 * Checks if the field value is falsely
	 */
	public function isFalse(): bool
	{
		return $this->toBool() === false;
	}

	/**
	 * Checks if the field content is not empty
	 */
	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Checks if the field value is truthy
	 */
	public function isTrue(): bool
	{
		return $this->toBool() === true;
	}

	/**
	 * Validates the field content with the given validator and parameters
	 */
	public function isValid(string $validator, ...$arguments): bool
	{
		return V::$validator($this->value, ...$arguments);
	}

	/**
	 * Returns the name of the field
	 */
	public function key(): string
	{
		return $this->key;
	}

	/**
	 * Returns the Kirby instance
	 * @since 5.1.0
	 */
	public function kirby(): App
	{
		return $this->parent?->kirby() ?? App::instance();
	}

	/**
	 * Parses all KirbyTags without also parsing Markdown
	 */
	public function kirbytags(): static
	{
		return $this->value(function ($value) {
			// Do not refactor as arrow function;
			// it needs to access the cloned field object as $this
			return $this->kirby()->kirbytags(
				text: $value,
				data: [
					'parent' => $this->parent(),
					'field'  => $this
				]
			);
		});
	}

	/**
	 * Converts the field content from Markdown/Kirbytext to valid HTML
	 */
	public function kirbytext(array $options = []): static
	{
		return $this->value(function ($value) use ($options) {
			// Do not refactor as arrow function;
			// it needs to access the cloned field object as $this
			return $this->kirby()->kirbytext(
				text:    $value,
				options: [
					...$options,
					'parent' => $this->parent(),
					'field'  => $this
				]
			);
		});
	}

	/**
	 * Converts the field content from inline Markdown/Kirbytext
	 * to valid HTML
	 * @since 3.1.0
	 */
	public function kirbytextInline(array $options = []): static
	{
		return $this->kirbytext(
			options: A::merge($options, ['markdown' => ['inline' => true]])
		);
	}

	/**
	 * Returns the length of the field content
	 */
	public function length(): int
	{
		return Str::length($this->value);
	}

	/**
	 * Converts the field content to lowercase
	 */
	public function lower(): static
	{
		return $this->value(fn ($value) => Str::lower($value));
	}

	/**
	 * Converts markdown to valid HTML
	 */
	public function markdown(array $options = []): static
	{
		return $this->value(
			fn ($value) => $this->kirby()->markdown($value, $options)
		);
	}

	/**
	 * @see self::parent()
	 */
	public function model(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Converts all line breaks in the field content to `<br>` tags.
	 * @since 3.3.0
	 */
	public function nl2br(): static
	{
		return $this->value(fn ($value) => nl2br($value ?? '', false));
	}

	/**
	 * Provides a fallback if the field value is empty
	 *
	 * @return $this|static
	 */
	public function or(mixed $fallback = null): static
	{
		if ($this->isNotEmpty() === true) {
			return $this;
		}

		if ($fallback instanceof self) {
			return $fallback;
		}

		return $this->value($fallback);
	}

	/**
	 * Returns the parent object of the field
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Parses the field value as DOM and replaces
	 * any permalinks in href/src attributes with
	 * the regular url
	 *
	 * This method is still experimental! You can use
	 * it to solve potential problems with permalinks
	 * already, but it might change in the future.
	 */
	public function permalinksToUrls(): static
	{
		$field = clone $this;

		if ($field->isNotEmpty() === true) {
			$dom        = new Dom($field->value);
			$attributes = ['href', 'src'];
			$elements   = $dom->query('//*[' . implode(' | ', A::map($attributes, fn ($attribute) => '@' . $attribute)) . ']');

			foreach ($elements as $element) {
				foreach ($attributes as $attribute) {
					if (
						$element->hasAttribute($attribute) &&
						$url = $element->getAttribute($attribute)
					) {
						try {
							$permalink = Permalink::from($url);

							if ($url = $permalink?->model()?->url()) {
								$element->setAttribute($attribute, $url);
							}
						} catch (InvalidArgumentException) {
							// ignore anything else than permalinks
						}
					}
				}
			}

			$field->value = $dom->toString();
		}

		return $field;
	}

	/**
	 * Uses the field value as Kirby query
	 */
	public function query(
		string|null $expect = null
	): mixed {
		if ($parent = $this->parent()) {
			return $parent->query($this->value, $expect);
		}

		return Str::query($this->value, [
			'kirby' => $app = $this->kirby(),
			'site'  => $app->site(),
			'page'  => $app->page()
		]);
	}

	/**
	 * It parses any queries found in the field value.
	 *
	 * @param string|null $fallback Fallback for tokens in the template that cannot be replaced (`null` to keep the original token)
	 */
	public function replace(
		array $data = [],
		string|null $fallback = ''
	): static {
		if ($parent = $this->parent()) {
			// Never pass `null` as the $template
			// to avoid the fallback to the model ID
			return $this->value(fn ($value) => $parent->toString(
				$value ?? '',
				$data,
				$fallback
			));
		}

		return $this->value(fn ($value) => Str::template(
			$value,
			[
				'kirby' => $app = $this->kirby(),
				'site'  => $app->site(),
				'page'  => $app->page(),
				...$data
			],
			['fallback' => $fallback]
		));
	}

	/**
	 * Cuts the string after the given length and
	 * adds "…" if it is longer
	 *
	 * @param int $length The number of characters in the string
	 * @param string $appendix An optional replacement for the missing rest
	 */
	public function short(
		int $length,
		string $appendix = '…'
	): static {
		return $this->value(
			fn ($value) => Str::short($value, $length, $appendix)
		);
	}

	/**
	 * Converts the field content to a slug
	 */
	public function slug(): static
	{
		return $this->value(fn ($value) => Str::slug($value));
	}

	/**
	 * Applies SmartyPants to the field
	 */
	public function smartypants(): static
	{
		return $this->value(fn ($value) => $this->kirby()->smartypants($value));
	}

	/**
	 * Splits the field content into an array
	 */
	public function split(string $separator = ','): array
	{
		return Str::split((string)$this->value, $separator);
	}

	/**
	 * Converts the Field object to an array
	 */
	public function toArray(): array
	{
		return [$this->key => $this->value];
	}

	/**
	 * Converts a yaml or json field to a Blocks object
	 */
	public function toBlocks(): Blocks
	{
		try {
			$blocks = Blocks::parse($this->value());
			$blocks = Blocks::factory($blocks, [
				'parent' => $this->parent(),
				'field'  => $this,
			]);
			return $blocks->filter('isHidden', false);
		} catch (Throwable) {
			$message = 'Invalid blocks data for "' . $this->key() . '" field';

			if ($parent = $this->parent()) {
				$message .= ' on parent "' . $parent->title() . '"';
			}

			throw new InvalidArgumentException(message: $message);
		}
	}

	/**
	 * Converts the field value into a proper boolean
	 *
	 * @param bool $default Default value if the field is empty
	 */
	public function toBool(bool $default = false): bool
	{
		$value = $this->isEmpty() ? $default : $this->value;
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Parses the field value with the given method
	 *
	 * @param string $method [',', 'yaml', 'json']
	 */
	public function toData(string $method = ','): array
	{
		return match ($method) {
			'yaml', 'json' => Data::decode($this->value, $method),
			default        => $this->split($method)
		};
	}

	/**
	 * Converts the field value to a timestamp or a formatted date
	 *
	 * @param string|\IntlDateFormatter|null $format PHP date formatting string
	 * @param string|null $fallback Fallback string for `strtotime`
	 */
	public function toDate(
		string|IntlDateFormatter|null $format = null,
		string|null $fallback = null
	): string|int|null {
		if (empty($this->value) === true && $fallback === null) {
			return null;
		}

		$time = match (empty($this->value)) {
			false => $this->toTimestamp(),
			true  => strtotime($fallback),
		};

		return Str::date($time, $format);
	}

	/**
	 * Parse yaml entries data and convert it to a
	 * collection of field objects
	 * @since 5.0.0
	 */
	public function toEntries(): Collection
	{
		$entries = new Collection(parent: $this->parent());

		foreach ($this->yaml() as $index => $entry) {
			$field = new Field($this->parent(), $index, $entry);
			$entries->append($field);
		}

		return $entries;
	}

	/**
	 * Returns a file object from a filename in the field
	 */
	public function toFile(): File|null
	{
		return $this->toFiles()->first();
	}

	/**
	 * Returns a file collection from a yaml list of filenames in the field
	 */
	public function toFiles(string $separator = 'yaml'): Files
	{
		$parent = $this->parent();
		$files  = new Files([]);

		foreach ($this->toData($separator) as $id) {
			if (
				is_string($id) === true &&
				$file = $parent->kirby()->file($id, $parent)
			) {
				$files->add($file);
			}
		}

		return $files;
	}

	/**
	 * Converts the field value into a proper float
	 *
	 * @param float $default Default value if the field is empty
	 */
	public function toFloat(float $default = 0): float
	{
		$value = $this->isEmpty() ? $default : $this->value;
		return (float)$value;
	}

	/**
	 * Converts the field value into a proper integer
	 *
	 * @param int $default Default value if the field is empty
	 */
	public function toInt(int $default = 0): int
	{
		$value = $this->isEmpty() ? $default : $this->value;
		return (int)$value;
	}

	/**
	 * Parse layouts and turn them into Layout objects
	 */
	public function toLayouts(): Layouts
	{
		$items = Layouts::parse($this->value());
		return Layouts::factory($items, [
			'parent' => $this->parent(),
			'field'  => $this,
		]);
	}

	/**
	 * Wraps a link tag around the field value.
	 * The field value is used as the link text
	 *
	 * @param mixed $attr1 Can be an optional URL. If no URL is set, the URL of the parent object will be used. Can also be an array of link attributes
	 * @param mixed $attr2 If `$attr1` is used to set the URL, you can use `$attr2` to pass an array of additional attributes.
	 */
	public function toLink(
		string|array|null $attr1 = null,
		array|null $attr2 = null
	): string {
		/**
		 * @var \Kirby\Cms\Page|\Kirby\Cms\Site $parent
		 */
		$parent   = $this->parent();
		$parent ??= $this->kirby()->site();

		if (is_string($attr1) === true) {
			$href = $attr1;
			$attr = $attr2;
		} else {
			$href = $parent->url();
			$attr = $attr1;
		}

		if ($parent->isActive() === true) {
			$attr['aria-current'] = 'page';
		}

		return Html::a($href, $this->value, $attr ?? []);
	}

	/**
	 * Parse yaml data and convert it to a content object
	 */
	public function toObject(): Content
	{
		return new Content($this->yaml(), $this->parent(), true);
	}

	/**
	 * Returns a page object from a page id in the field
	 */
	public function toPage(): Page|null
	{
		return $this->toPages()->first();
	}

	/**
	 * Returns a pages collection from a yaml list of page ids in the field
	 *
	 * @param string $separator Can be any other separator to split the field value by
	 */
	public function toPages(string $separator = 'yaml'): Pages
	{
		return $this->kirby()->site()->find(
			false,
			false,
			...$this->toData($separator)
		);
	}

	/**
	 * Turns the field value into an QR code object
	 */
	public function toQrCode(): QrCode|null
	{
		return $this->isNotEmpty() ? new QrCode($this->value) : null;
	}

	/**
	 * Returns the field value as string
	 */
	public function toString(): string
	{
		return (string)$this->value;
	}

	/**
	 * Converts a yaml field to a Structure object
	 */
	public function toStructure(): Structure
	{
		try {
			$items = Data::decode($this->value, 'yaml');
			return Structure::factory($items, [
				'parent' => $this->parent(),
				'field'  => $this
			]);
		} catch (Throwable) {
			$message = 'Invalid structure data for "' . $this->key() . '" field';

			if ($parent = $this->parent()) {
				$message .= ' on parent "' . $parent->id() . '"';
			}

			throw new InvalidArgumentException(message: $message);
		}
	}

	/**
	 * Converts the field value to a Unix timestamp
	 */
	public function toTimestamp(): int|false
	{
		return strtotime($this->value ?? '');
	}

	/**
	 * Turns the field value into an absolute Url
	 */
	public function toUrl(): string|null
	{
		try {
			return $this->isNotEmpty() ? Url::to($this->value) : null;
		} catch (NotFoundException) {
			return null;
		}
	}

	/**
	 * Converts a user email address to a user object
	 */
	public function toUser(): User|null
	{
		return $this->toUsers()->first();
	}

	/**
	 * Returns a users collection from a yaml list
	 * of user email addresses in the field
	 */
	public function toUsers(string $separator = 'yaml'): Users
	{
		return $this->kirby()->users()->find(
			false,
			false,
			...$this->toData($separator)
		);
	}

	/**
	 * Converts the field content to uppercase
	 */
	public function upper(): static
	{
		return $this->value(fn ($value) => Str::upper($value));
	}

	/**
	 * Returns the field content. If a new value is passed,
	 * the modified field will be returned. Otherwise it
	 * will return the field value.
	 */
	public function value(string|Closure|null $value = null): mixed
	{
		if ($value === null) {
			return $this->value;
		}

		$clone = clone $this;

		if ($value instanceof Closure) {
			$value = $value->call($clone, $clone->value);
		}

		$clone->value = (string)$value;

		return $clone;
	}

	/**
	 * Avoids typographical widows in strings by replacing
	 * the last space with `&nbsp;`
	 */
	public function widont(): static
	{
		return $this->value(fn ($value) => Str::widont($value));
	}

	/**
	 * Returns the number of words in the text
	 */
	public function words(): int
	{
		$text = strip_tags($this->value ?? '');
		return str_word_count($text);
	}

	/**
	 * Converts the field content to valid XML
	 */
	public function xml(): static
	{
		return $this->value(fn ($value) => Xml::encode($value));
	}

	/**
	 * Parses yaml in the field content and returns an array
	 */
	public function yaml(): array
	{
		return $this->toData('yaml');
	}

	/**
	 * Aliases
	 */

	/**
	 * @see self::toBool()
	 */
	public function bool(bool $default = false): bool
	{
		return $this->toBool($default);
	}

	/**
	 * @see self::escape()
	 */
	public function esc(string $context = 'html'): static
	{
		return $this->escape($context);
	}

	/**
	 * @see self::float()
	 */
	public function float(float $default = 0): float
	{
		return $this->toFloat($default);
	}

	/**
	 * @see self::html()
	 */
	public function h(): static
	{
		return $this->html();
	}

	/**
	 * @see self::int()
	 */
	public function int(int $default = 0): int
	{
		return $this->toInt($default);
	}

	/**
	 * @see self::kirbytext()
	 */
	public function kt(array $options = []): static
	{
		return $this->kirbytext($options);
	}

	/**
	 * @see self::kirbytextinline()
	 */
	public function kti(array $options = []): static
	{
		return $this->kirbytextinline($options);
	}

	/**
	 * @see self::toLink()
	 */
	public function link(
		string|array|null $attr1 = null,
		array|null $attr2 = null
	): string {
		return $this->toLink($attr1, $attr2);
	}

	/**
	 * @see self::markdown()
	 */
	public function md(array $options = []): static
	{
		return $this->markdown($options);
	}

	/**
	 * @see self::smartypants()
	 */
	public function sp(): static
	{
		return $this->smartypants();
	}

	/**
	 * @see self::isValid()
	 */
	public function v(string $validator, ...$arguments): bool
	{
		return $this->isValid($validator, ...$arguments);
	}

	/**
	 * @see self::xml()
	 */
	public function x(): static
	{
		return $this->xml();
	}
}
