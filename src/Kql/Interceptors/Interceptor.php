<?php

namespace Kirby\Kql\Interceptors;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\PermissionException;
use Kirby\Kql\Help;
use Kirby\Kql\Kql;
use Kirby\Toolkit\Str;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

abstract class Interceptor
{
	public const CLASS_ALIAS = null;

	protected $toArray = [];

	public function __construct(protected $object)
	{}

	public function __call($method, array $args = [])
	{
		if ($this->isAllowedMethod($method) === true) {
			return $this->object->$method(...$args);
		}

		$this->forbiddenMethod($method);
	}

	public function allowedMethods(): array
	{
		return [];
	}

	protected function forbiddenMethod(string $method)
	{
		$className = get_class($this->object);
		throw new PermissionException('The method "' . $className . '::' . $method . '()" is not allowed in the API context');
	}

	/**
	 * Returns a registered method by name, either from
	 * the current class or from a parent class ordered by
	 * inheritance order (top to bottom)
	 *
	 * @param string $method
	 * @return \Closure|null
	 */
	protected function getMethod(string $method)
	{
		if (isset($this->object::$methods[$method]) === true) {
			return $this->object::$methods[$method];
		}

		foreach (class_parents($this->object) as $parent) {
			if (isset($parent::$methods[$method]) === true) {
				return $parent::$methods[$method];
			}
		}

		return null;
	}

	protected function isAllowedCallable($method): bool
	{
		try {
			if (is_a($method, 'Closure') === true) {
				$ref = new ReflectionFunction($method);
			} elseif (is_string($method) === true) {
				$ref = new ReflectionMethod($this->object, $method);
			} else {
				throw new Exception('Invalid method');
			}

			if ($comment = $ref->getDocComment()) {
				if (Str::contains($comment, '@kql-allowed') === true) {
					return true;
				}
			}
		} catch (Throwable $e) {
			return false;
		}

		return false;
	}

	protected function isAllowedMethod($method)
	{
		$kirby    = App::instance();
		$fullName = strtolower(get_class($this->object) . '::' . $method);
		$blocked  = array_map('strtolower', $kirby->option('kql.methods.blocked', []));

		// check in the block list from the config
		if (in_array($fullName, $blocked) === true) {
			return false;
		}

		// check in class allow list
		if (in_array($method, $this->allowedMethods()) === true) {
			return true;
		}

		$allowed = array_map('strtolower', $kirby->option('kql.methods.allowed', []));

		// check in the allow list from the config
		if (in_array($fullName, $allowed) === true) {
			return true;
		}

		// support for model methods with docblock comment
		if ($this->isAllowedCallable($method) === true) {
			return true;
		}

		// support for custom methods with docblock comment
		if ($this->isAllowedCustomMethod($method) === true) {
			return true;
		}

		return false;
	}

	protected function isAllowedCustomMethod(string $method): bool
	{
		// has no custom methods
		if (property_exists($this->object, 'methods') === false) {
			return false;
		}

		// does not have that method
		if (!$call = $this->getMethod($method)) {
			return false;
		}

		// check for a docblock comment
		if ($this->isAllowedCallable($call) === true) {
			return true;
		}

		return false;
	}

	public function __debugInfo(): array
	{
		return [
			'type'    => $this::CLASS_ALIAS,
			'methods' => Help::forMethods($this->object, $this->allowedMethods()),
			'value'   => $this->toArray()
		];
	}

	public function toArray(): array|null
	{
		$toArray = [];

		// filter methods which cannot be called
		foreach ($this->toArray as $method) {
			if ($this->isAllowedMethod($method) === true) {
				$toArray[] = $method;
			}
		}

		return Kql::select($this, $toArray);
	}

	public function toResponse(): array|null
	{
		return $this->toArray();
	}
}
