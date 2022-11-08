<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;

/**
 * Wrapper around our Email package, which
 * handles all the magic connections between Kirby
 * and sending emails, like email templates, file
 * attachments, etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Email
{
	/**
	 * Options configured through the `email` CMS option
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Props for the email object; will be passed to the
	 * Kirby\Email\Email class
	 *
	 * @var array
	 */
	protected $props;

	/**
	 * Class constructor
	 *
	 * @param string|array $preset Preset name from the config or a simple props array
	 * @param array $props Props array to override the $preset
	 */
	public function __construct($preset = [], array $props = [])
	{
		$this->options = App::instance()->option('email');

		// build a prop array based on preset and props
		$preset = $this->preset($preset);
		$this->props = array_merge($preset, $props);

		// add transport settings
		$this->props['transport'] ??= $this->options['transport'] ?? [];

		// add predefined beforeSend option
		$this->props['beforeSend'] ??= $this->options['beforeSend'] ?? null;

		// transform model objects to values
		$this->transformUserSingle('from', 'fromName');
		$this->transformUserSingle('replyTo', 'replyToName');
		$this->transformUserMultiple('to');
		$this->transformUserMultiple('cc');
		$this->transformUserMultiple('bcc');
		$this->transformFile('attachments');

		// load template for body text
		$this->template();
	}

	/**
	 * Grabs a preset from the options; supports fixed
	 * prop arrays in case a preset is not needed
	 *
	 * @param string|array $preset Preset name or simple prop array
	 * @return array
	 * @throws \Kirby\Exception\NotFoundException
	 */
	protected function preset($preset): array
	{
		// only passed props, not preset name
		if (is_array($preset) === true) {
			return $preset;
		}

		// preset does not exist
		if (isset($this->options['presets'][$preset]) !== true) {
			throw new NotFoundException([
				'key'  => 'email.preset.notFound',
				'data' => ['name' => $preset]
			]);
		}

		return $this->options['presets'][$preset];
	}

	/**
	 * Renders the email template(s) and sets the body props
	 * to the result
	 *
	 * @return void
	 * @throws \Kirby\Exception\NotFoundException
	 */
	protected function template(): void
	{
		if (isset($this->props['template']) === true) {
			// prepare data to be passed to template
			$data = $this->props['data'] ?? [];

			// check if html/text templates exist
			$html = $this->getTemplate($this->props['template'], 'html');
			$text = $this->getTemplate($this->props['template'], 'text');

			if ($html->exists()) {
				$this->props['body'] = [
					'html' => $html->render($data)
				];

				if ($text->exists()) {
					$this->props['body']['text'] = $text->render($data);
				}

			// fallback to single email text template
			} elseif ($text->exists()) {
				$this->props['body'] = $text->render($data);
			} else {
				throw new NotFoundException('The email template "' . $this->props['template'] . '" cannot be found');
			}
		}
	}

	/**
	 * Returns an email template by name and type
	 *
	 * @param string $name Template name
	 * @param string|null $type `html` or `text`
	 * @return \Kirby\Template\Template
	 */
	protected function getTemplate(string $name, string $type = null)
	{
		return App::instance()->template('emails/' . $name, $type, 'text');
	}

	/**
	 * Returns the prop array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->props;
	}

	/**
	 * Transforms file object(s) to an array of file roots;
	 * supports simple strings, file objects or collections/arrays of either
	 *
	 * @param string $prop Prop to transform
	 * @return void
	 */
	protected function transformFile(string $prop): void
	{
		$this->props[$prop] = $this->transformModel($prop, 'Kirby\Cms\File', 'root');
	}

	/**
	 * Transforms Kirby models to a simplified collection
	 *
	 * @param string $prop Prop to transform
	 * @param string $class Fully qualified class name of the supported model
	 * @param string $contentValue Model method that returns the array value
	 * @param string|null $contentKey Optional model method that returns the array key;
	 *                                returns a simple value-only array if not given
	 * @return array Simple key-value or just value array with the transformed prop data
	 */
	protected function transformModel(string $prop, string $class, string $contentValue, string $contentKey = null): array
	{
		$value = $this->props[$prop] ?? [];

		// ensure consistent input by making everything an iterable value
		if (is_iterable($value) !== true) {
			$value = [$value];
		}

		$result = [];
		foreach ($value as $key => $item) {
			if (is_string($item) === true) {
				// value is already a string
				if ($contentKey !== null && is_string($key) === true) {
					$result[$key] = $item;
				} else {
					$result[] = $item;
				}
			} elseif ($item instanceof $class) {
				// value is a model object, get value through content method(s)
				if ($contentKey !== null) {
					$result[(string)$item->$contentKey()] = (string)$item->$contentValue();
				} else {
					$result[] = (string)$item->$contentValue();
				}
			} else {
				// invalid input
				throw new InvalidArgumentException('Invalid input for prop "' . $prop . '", expected string or "' . $class . '" object or collection');
			}
		}

		return $result;
	}

	/**
	 * Transforms an user object to the email address and name;
	 * supports simple strings, user objects or collections/arrays of either
	 * (note: only the first item in a collection/array will be used)
	 *
	 * @param string $addressProp Prop with the email address
	 * @param string $nameProp Prop with the name corresponding to the $addressProp
	 * @return void
	 */
	protected function transformUserSingle(string $addressProp, string $nameProp): void
	{
		$result = $this->transformModel($addressProp, 'Kirby\Cms\User', 'name', 'email');

		$address = array_keys($result)[0] ?? null;
		$name    = $result[$address] ?? null;

		// if the array is non-associative, the value is the address
		if (is_int($address) === true) {
			$address = $name;
			$name    = null;
		}

		// always use the address as we have transformed that prop above
		$this->props[$addressProp] = $address;

		// only use the name from the user if no custom name was set
		$this->props[$nameProp] ??= $name;
	}

	/**
	 * Transforms user object(s) to the email address(es) and name(s);
	 * supports simple strings, user objects or collections/arrays of either
	 *
	 * @param string $prop Prop to transform
	 * @return void
	 */
	protected function transformUserMultiple(string $prop): void
	{
		$this->props[$prop] = $this->transformModel($prop, 'Kirby\Cms\User', 'name', 'email');
	}
}
