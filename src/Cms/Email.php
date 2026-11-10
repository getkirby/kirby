<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Template\Template;

/**
 * Wrapper around our Email package, which
 * handles all the magic connections between Kirby
 * and sending emails, like email templates, file
 * attachments, etc.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Email
{
	/**
	 * Options configured through the `email` CMS option
	 *
	 * @var array<string, mixed>
	 */
	protected array $options;

	/**
	 * Props for the email object; will be passed to the
	 * \Kirby\Email\Email class
	 *
	 * @var array<string, mixed>
	 */
	protected array $props;

	/**
	 * Class constructor
	 *
	 * @param $preset Preset name from the config or a simple props array
	 * @param $props Props array to override the $preset
	 */
	public function __construct(string|array $preset = [], array $props = [])
	{
		$this->options = App::instance()->option('email', []);
		$this->props   = [...$this->preset($preset), ...$props];

		$this->props['transport']  ??= $this->options['transport'] ?? [];
		$this->props['beforeSend'] ??= $this->options['beforeSend'] ?? null;

		// transform model objects to values
		$this->transformUserSingle('from', 'fromName');
		$this->transformUserSingle('replyTo', 'replyToName');
		$this->transformUserMultiple('to');
		$this->transformUserMultiple('cc');
		$this->transformUserMultiple('bcc');
		$this->transformFile('attachments');

		$this->template();
	}

	/**
	 * Grabs a preset from the options; supports fixed
	 * prop arrays in case a preset is not needed
	 *
	 * @param $preset Preset name or simple prop array
	 * @throws NotFoundException
	 */
	protected function preset(string|array $preset): array
	{
		if (is_array($preset) === true) {
			return $preset;
		}

		if (isset($this->options['presets'][$preset]) !== true) {
			throw new NotFoundException(
				key: 'email.preset.notFound',
				data: ['name' => $preset]
			);
		}

		return $this->options['presets'][$preset];
	}

	/**
	 * Renders the email template(s) and sets the body props
	 * to the result
	 *
	 * @throws NotFoundException
	 */
	protected function template(): void
	{
		if (isset($this->props['template']) === false) {
			return;
		}

		$data = $this->props['data'] ?? [];
		$html = $this->getTemplate($this->props['template'], 'html');
		$text = $this->getTemplate($this->props['template'], 'text');

		if ($html->exists() === true) {
			$this->props['body'] = ['html' => $html->render($data)];

			if ($text->exists() === true) {
				$this->props['body']['text'] = $text->render($data);
			}

			return;
		}

		// fallback to single email text template
		if ($text->exists() === true) {
			$this->props['body'] = $text->render($data);
			return;
		}

		throw new NotFoundException(
			message: 'The email template "' . $this->props['template'] . '" cannot be found'
		);
	}

	/**
	 * Returns an email template by name and type
	 */
	protected function getTemplate(string $name, string|null $type = null): Template
	{
		return App::instance()->template('emails/' . $name, $type, 'text');
	}

	/**
	 * Returns the prop array
	 */
	public function toArray(): array
	{
		return $this->props;
	}

	/**
	 * Transforms file object(s) to an array of file roots;
	 * supports simple strings, file objects or collections/arrays of either
	 *
	 * @param $prop Prop to transform
	 */
	protected function transformFile(string $prop): void
	{
		$this->props[$prop] = $this->transformModel($prop, File::class, 'root');
	}

	/**
	 * Transforms Kirby models to a simplified collection
	 *
	 * @param $prop Prop to transform
	 * @param class-string<File|User> $class Fully qualified class name of the supported model
	 * @param $contentValue Model method that returns the array value
	 * @param $contentKey Optional model method that returns the array key;
	 *                   returns a simple value-only array if not given
	 * @return array<string|int, string> Simple key-value or just value array with the transformed prop data
	 */
	protected function transformModel(
		string $prop,
		string $class,
		string $contentValue,
		string|null $contentKey = null
	): array {
		$value = $this->props[$prop] ?? [];

		// ensure consistent input by making everything an iterable value
		if (is_iterable($value) !== true) {
			$value = [$value];
		}

		$result = [];
		foreach ($value as $key => $item) {
			if (is_string($item) === true) {
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
				throw new InvalidArgumentException(
					message: 'Invalid input for prop "' . $prop . '", expected string or "' . $class . '" object or collection'
				);
			}
		}

		return $result;
	}

	/**
	 * Transforms a user object to the email address and name;
	 * supports simple strings, user objects or collections/arrays of either
	 * (note: only the first item in a collection/array will be used)
	 *
	 * @param $addressProp Prop with the email address
	 * @param $nameProp Prop with the name corresponding to the $addressProp
	 */
	protected function transformUserSingle(
		string $addressProp,
		string $nameProp
	): void {
		$result = $this->transformModel($addressProp, User::class, 'name', 'email');

		$address = array_keys($result)[0] ?? null;
		$name    = $result[$address ?? ''] ?? null;

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
	 * @param $prop Prop to transform
	 */
	protected function transformUserMultiple(string $prop): void
	{
		$this->props[$prop] = $this->transformModel(
			$prop,
			User::class,
			'name',
			'email'
		);
	}
}
