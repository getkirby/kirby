<?php

namespace Kirby\Form;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Abstract field class to be used instead
 * of functional field components for more
 * control.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
class FieldReflector
{
	protected array $properties = [];
	protected ReflectionClass $reflection;

	public function __construct(
		protected string $field
	) {
		// get all arguments for the field class constructor
		$this->reflection = new ReflectionClass($field);
		$this->properties = $this->parseConstructor($this->reflection->getConstructor());

		dump($this->properties);
	}

	protected function parseConstructor(ReflectionMethod|null $constructor): array
	{
		if ($constructor === null) {
			return [];
		}

		$properties = [];
		$internal   = ['name', 'type', 'model', 'siblings'];

		// get the matching class property for each argument if it exists
		foreach ($constructor->getParameters() as $param) {

			if (in_array($param->name, $internal)) {
				continue;
			}

			$properties[$param->name] = [
				'name'     => $param->name,
				'type'     => $param->getType()?->__toString(),
				'required' => !$param->isOptional(),
				'default'  => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
				...$this->parseProperty($this->reflection->getProperty($param->name))
			];
		}

		ksort($properties);

		return $properties;
	}

	protected function parseProperty(ReflectionProperty|null $property): array
	{
		if ($property === null) {
			return [];
		}

		$comment = $property->getDocComment();

		if ($comment === false) {
			return [];
		}

		// parse the comment. extract the tags and description
		$tags        = [];
		$description = '';
		$lines       = explode("\n", $comment);

		foreach ($lines as $line) {
			$line = trim($line);

			$line = str_replace('/**', '', $line);
			$line = str_replace('*/', '', $line);
			$line = preg_replace('/^\s*\*\s*/', '', $line);

			if (strpos($line, '@') === 0) {
					$tags[] = trim(str_replace('@', '', $line), ' ');
			} else {
				$description .= $line;
			}
		}

		return [
			'tags'        => $tags,
			'description' => $description,
		];
	}
}
