<?php

use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Html;
use Kirby\Cms\Layouts;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Structure;
use Kirby\Cms\Url;
use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\Content\Content;
use Kirby\Content\Field;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Image\QrCode;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Dom;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Kirby\Toolkit\Xml;
use Kirby\Uuid\Uuid;

/**
 * Field method setup
 */
return function (App $app) {
	return [

		// states

		/**
		 * Converts the field value into a proper boolean and inverts it
		 */
		'isFalse' => function (Field $field): bool {
			return $field->toBool() === false;
		},

		/**
		 * Converts the field value into a proper boolean
		 */
		'isTrue' => function (Field $field): bool {
			return $field->toBool() === true;
		},

		/**
		 * Validates the field content with the given validator and parameters
		 *
		 * @param mixed ...$arguments A list of optional validator arguments
		 */
		'isValid' => function (
			Field $field,
			string $validator,
			...$arguments
		): bool {
			return V::$validator($field->value, ...$arguments);
		},

		// converters
		/**
		 * Converts a yaml or json field to a Blocks object
		 */
		'toBlocks' => function (Field $field): Blocks {
			try {
				$blocks = Blocks::parse($field->value());
				$blocks = Blocks::factory($blocks, [
					'parent' => $field->parent(),
					'field'  => $field,
				]);
				return $blocks->filter('isHidden', false);
			} catch (Throwable) {
				$message = 'Invalid blocks data for "' . $field->key() . '" field';

				if ($parent = $field->parent()) {
					$message .= ' on parent "' . $parent->title() . '"';
				}

				throw new InvalidArgumentException($message);
			}
		},

		/**
		 * Converts the field value into a proper boolean
		 *
		 * @param bool $default Default value if the field is empty
		 */
		'toBool' => function (Field $field, bool $default = false): bool {
			$value = $field->isEmpty() ? $default : $field->value;
			return filter_var($value, FILTER_VALIDATE_BOOLEAN);
		},

		/**
		 * Parses the field value with the given method
		 *
		 * @param string $method [',', 'yaml', 'json']
		 */
		'toData' => function (Field $field, string $method = ','): array {
			return match ($method) {
				'yaml', 'json' => Data::decode($field->value, $method),
				default        => $field->split($method)
			};
		},

		/**
		 * Converts the field value to a timestamp or a formatted date
		 *
		 * @param string|\IntlDateFormatter|null $format PHP date formatting string
		 * @param string|null $fallback Fallback string for `strtotime`
		 */
		'toDate' => function (
			Field $field,
			string|IntlDateFormatter|null $format = null,
			string $fallback = null
		) use ($app): string|int|null {
			if (empty($field->value) === true && $fallback === null) {
				return null;
			}

			if (empty($field->value) === false) {
				$time = $field->toTimestamp();
			} else {
				$time = strtotime($fallback);
			}

			return Str::date($time, $format);
		},

		/**
		 * Returns a file object from a filename in the field
		 */
		'toFile' => function (Field $field): File|null {
			return $field->toFiles()->first();
		},

		/**
		 * Returns a file collection from a yaml list of filenames in the field
		 */
		'toFiles' => function (
			Field $field,
			string $separator = 'yaml'
		): Files {
			$parent = $field->parent();
			$files  = new Files([]);

			foreach ($field->toData($separator) as $id) {
				if ($file = $parent->kirby()->file($id, $parent)) {
					$files->add($file);
				}
			}

			return $files;
		},

		/**
		 * Converts the field value into a proper float
		 *
		 * @param float $default Default value if the field is empty
		 */
		'toFloat' => function (Field $field, float $default = 0): float {
			$value = $field->isEmpty() ? $default : $field->value;
			return (float)$value;
		},

		/**
		 * Converts the field value into a proper integer
		 *
		 * @param int $default Default value if the field is empty
		 */
		'toInt' => function (Field $field, int $default = 0): int {
			$value = $field->isEmpty() ? $default : $field->value;
			return (int)$value;
		},

		/**
		 * Parse layouts and turn them into Layout objects
		 */
		'toLayouts' => function (Field $field): Layouts {
			return Layouts::factory(Layouts::parse($field->value()), [
				'parent' => $field->parent(),
				'field'  => $field,
			]);
		},

		/**
		 * Wraps a link tag around the field value. The field value is used as the link text
		 *
		 * @param mixed $attr1 Can be an optional Url. If no Url is set, the Url of the Page, File or Site will be used. Can also be an array of link attributes
		 * @param mixed $attr2 If `$attr1` is used to set the Url, you can use `$attr2` to pass an array of additional attributes.
		 */
		'toLink' => function (
			Field $field,
			string|array|null $attr1 = null,
			array|null $attr2 = null
		): string {
			if (is_string($attr1) === true) {
				$href = $attr1;
				$attr = $attr2;
			} else {
				$href = $field->parent()->url();
				$attr = $attr1;
			}

			if ($field->parent()->isActive()) {
				$attr['aria-current'] = 'page';
			}

			return Html::a($href, $field->value, $attr ?? []);
		},

		/**
		 * Parse yaml data and convert it to a
		 * content object
		 */
		'toObject' => function (Field $field): Content {
			return new Content($field->yaml(), $field->parent(), true);
		},

		/**
		 * Returns a page object from a page id in the field
		 */
		'toPage' => function (Field $field): Page|null {
			return $field->toPages()->first();
		},

		/**
		 * Returns a pages collection from a yaml list of page ids in the field
		 *
		 * @param string $separator Can be any other separator to split the field value by
		 */
		'toPages' => function (
			Field $field,
			string $separator = 'yaml'
		) use ($app): Pages {
			return $app->site()->find(
				false,
				false,
				...$field->toData($separator)
			);
		},

		/**
		 * Turns the field value into an QR code object
		 */
		'toQrCode' => function (Field $field): QrCode|null {
			return $field->isNotEmpty() ? new QrCode($field->value) : null;
		},

		/**
		 * Converts a yaml field to a Structure object
		 */
		'toStructure' => function (Field $field): Structure {
			try {
				return Structure::factory(
					Data::decode($field->value, 'yaml'),
					['parent' => $field->parent(), 'field' => $field]
				);
			} catch (Exception) {
				$message = 'Invalid structure data for "' . $field->key() . '" field';

				if ($parent = $field->parent()) {
					$message .= ' on parent "' . $parent->id() . '"';
				}

				throw new InvalidArgumentException($message);
			}
		},

		/**
		 * Converts the field value to a Unix timestamp
		 */
		'toTimestamp' => function (Field $field): int|false {
			return strtotime($field->value ?? '');
		},

		/**
		 * Turns the field value into an absolute Url
		 */
		'toUrl' => function (Field $field): string|null {
			try {
				return $field->isNotEmpty() ? Url::to($field->value) : null;
			} catch (NotFoundException) {
				return null;
			}
		},

		/**
		 * Converts a user email address to a user object
		 */
		'toUser' => function (Field $field): User|null {
			return $field->toUsers()->first();
		},

		/**
		 * Returns a users collection from a yaml list
		 * of user email addresses in the field
		 */
		'toUsers' => function (
			Field $field,
			string $separator = 'yaml'
		) use ($app): Users {
			return $app->users()->find(
				false,
				false,
				...$field->toData($separator)
			);
		},

		// inspectors

		/**
		 * Returns the length of the field content
		 */
		'length' => function (Field $field): int {
			return Str::length($field->value);
		},

		/**
		 * Returns the number of words in the text
		 */
		'words' => function (Field $field): int {
			return str_word_count(strip_tags($field->value ?? ''));
		},

		// manipulators

		/**
		 * Applies the callback function to the field
		 * @since 3.4.0
		 */
		'callback' => function (Field $field, Closure $callback): mixed {
			return $callback($field);
		},

		/**
		 * Escapes the field value to be safely used in HTML
		 * templates without the risk of XSS attacks
		 *
		 * @param string $context Location of output (`html`, `attr`, `js`, `css`, `url` or `xml`)
		 */
		'escape' => function (Field $field, string $context = 'html'): Field {
			$field->value = Str::esc($field->value ?? '', $context);
			return $field;
		},

		/**
		 * Creates an excerpt of the field value without html
		 * or any other formatting.
		 */
		'excerpt' => function (
			Field $field,
			int $chars = 0,
			bool $strip = true,
			string $rep = ' …'
		): Field {
			$field->value = Str::excerpt(
				$field->kirbytext()->value(),
				$chars,
				$strip,
				$rep
			);
			return $field;
		},

		/**
		 * Converts the field content to valid HTML
		 */
		'html' => function (Field $field): Field {
			$field->value = Html::encode($field->value);
			return $field;
		},

		/**
		 * Strips all block-level HTML elements from the field value,
		 * it can be safely placed inside of other inline elements
		 * without the risk of breaking the HTML structure.
		 * @since 3.3.0
		 */
		'inline' => function (Field $field): Field {
			// List of valid inline elements taken from: https://developer.mozilla.org/de/docs/Web/HTML/Inline_elemente
			// Obsolete elements, script tags, image maps and form elements have
			// been excluded for safety reasons and as they are most likely not
			// needed in most cases.
			$field->value = strip_tags($field->value ?? '', Html::$inlineList);
			return $field;
		},

		/**
		 * Converts the field content from Markdown/Kirbytext to valid HTML
		 */
		'kirbytext' => function (
			Field $field,
			array $options = []
		) use ($app): Field {
			$field->value = $app->kirbytext($field->value, A::merge($options, [
				'parent' => $field->parent(),
				'field'  => $field
			]));

			return $field;
		},

		/**
		 * Converts the field content from inline Markdown/Kirbytext
		 * to valid HTML
		 * @since 3.1.0
		 */
		'kirbytextinline' => function (
			Field $field,
			array $options = []
		) use ($app): Field {
			$field->value = $app->kirbytext($field->value, A::merge($options, [
				'parent'   => $field->parent(),
				'field'    => $field,
				'markdown' => [
					'inline' => true
				]
			]));

			return $field;
		},

		/**
		 * Parses all KirbyTags without also parsing Markdown
		 */
		'kirbytags' => function (Field $field) use ($app): Field {
			$field->value = $app->kirbytags($field->value, [
				'parent' => $field->parent(),
				'field'  => $field
			]);

			return $field;
		},

		/**
		 * Converts the field content to lowercase
		 */
		'lower' => function (Field $field): Field {
			$field->value = Str::lower($field->value);
			return $field;
		},

		/**
		 * Converts markdown to valid HTML
		 */
		'markdown' => function (
			Field $field,
			array $options = []
		) use ($app): Field {
			$field->value = $app->markdown($field->value, $options);
			return $field;
		},

		/**
		 * Converts all line breaks in the field content to `<br>` tags.
		 * @since 3.3.0
		 */
		'nl2br' => function (Field $field): Field {
			$field->value = nl2br($field->value ?? '', false);
			return $field;
		},

		/**
		 * Parses the field value as DOM and replaces
		 * any permalinks in href/src attributes with
		 * the regular url
		 *
		 * This method is still experimental! You can use
		 * it to solve potential problems with permalinks
		 * already, but it might change in the future.
		 */
		'permalinksToUrls' => function (Field $field): Field {
			if ($field->isNotEmpty() === true) {
				$dom        = new Dom($field->value);
				$attributes = ['href', 'src'];
				$elements   = $dom->query('//*[' . implode(' | ', A::map($attributes, fn ($attribute) => '@' . $attribute)) . ']');

				foreach ($elements as $element) {
					foreach ($attributes as $attribute) {
						if ($element->hasAttribute($attribute) && $url = $element->getAttribute($attribute)) {
							try {
								if ($uuid = Uuid::for($url)) {
									$url = $uuid->model()?->url();
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
		},

		/**
		 * Uses the field value as Kirby query
		 */
		'query' => function (
			Field $field,
			string $expect = null
		) use ($app): mixed {
			if ($parent = $field->parent()) {
				return $parent->query($field->value, $expect);
			}

			return Str::query($field->value, [
				'kirby' => $app,
				'site'  => $app->site(),
				'page'  => $app->page()
			]);
		},

		/**
		 * It parses any queries found in the field value.
		 *
		 * @param string|null $fallback Fallback for tokens in the template that cannot be replaced (`null` to keep the original token)
		 */
		'replace' => function (
			Field $field,
			array $data = [],
			string|null $fallback = ''
		) use ($app): Field {
			if ($parent = $field->parent()) {
				// never pass `null` as the $template to avoid the fallback to the model ID
				$field->value = $parent->toString($field->value ?? '', $data, $fallback);
			} else {
				$field->value = Str::template($field->value, array_replace([
					'kirby' => $app,
					'site'  => $app->site(),
					'page'  => $app->page()
				], $data), ['fallback' => $fallback]);
			}

			return $field;
		},

		/**
		 * Cuts the string after the given length and
		 * adds "…" if it is longer
		 *
		 * @param int $length The number of characters in the string
		 * @param string $appendix An optional replacement for the missing rest
		 */
		'short' => function (
			Field $field,
			int $length,
			string $appendix = '…'
		): Field {
			$field->value = Str::short($field->value, $length, $appendix);
			return $field;
		},

		/**
		 * Converts the field content to a slug
		 */
		'slug' => function (Field $field): Field {
			$field->value = Str::slug($field->value);
			return $field;
		},

		/**
		 * Applies SmartyPants to the field
		 */
		'smartypants' => function (Field $field) use ($app): Field {
			$field->value = $app->smartypants($field->value);
			return $field;
		},

		/**
		 * Splits the field content into an array
		 */
		'split' => function (Field $field, $separator = ','): array {
			return Str::split((string)$field->value, $separator);
		},

		/**
		 * Converts the field content to uppercase
		 */
		'upper' => function (Field $field): Field {
			$field->value = Str::upper($field->value);
			return $field;
		},

		/**
		 * Avoids typographical widows in strings by replacing
		 * the last space with `&nbsp;`
		 */
		'widont' => function (Field $field): Field {
			$field->value = Str::widont($field->value);
			return $field;
		},

		/**
		 * Converts the field content to valid XML
		 */
		'xml' => function (Field $field): Field {
			$field->value = Xml::encode($field->value);
			return $field;
		},

		// aliases

		/**
		 * Parses yaml in the field content and returns an array
		 */
		'yaml' => function (Field $field): array {
			return $field->toData('yaml');
		},

	];
};
