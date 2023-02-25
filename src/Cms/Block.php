<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Represents a single block
 * which can be inspected further or
 * converted to HTML
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Block extends Item
{
	use HasMethods;

	public const ITEMS_CLASS = Blocks::class;

	/**
	 * @var \Kirby\Cms\Content
	 */
	protected $content;

	/**
	 * @var bool
	 */
	protected $isHidden;

	/**
	 * Registry with all block models
	 *
	 * @var array
	 */
	public static $models = [];

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * Proxy for content fields
	 *
	 * @param string $method
	 * @param array $args
	 * @return \Kirby\Cms\Field
	 */
	public function __call(string $method, array $args = [])
	{
		// block methods
		if ($this->hasMethod($method)) {
			return $this->callMethod($method, $args);
		}

		return $this->content()->get($method);
	}

	/**
	 * Creates a new block object
	 *
	 * @param array $params
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(array $params)
	{
		parent::__construct($params);

		// @deprecated import old builder format
		// @todo block.converter remove eventually
		// @codeCoverageIgnoreStart
		$params = BlockConverter::builderBlock($params);
		$params = BlockConverter::editorBlock($params);
		// @codeCoverageIgnoreEnd

		if (isset($params['type']) === false) {
			throw new InvalidArgumentException('The block type is missing');
		}

		// make sure the content is always defined as array to keep
		// at least a bit of backward compatibility with older fields
		if (is_array($params['content'] ?? null) === false) {
			$params['content'] = [];
		}

		$this->content  = $params['content'];
		$this->isHidden = $params['isHidden'] ?? false;
		$this->type     = $params['type'];

		// create the content object
		$this->content = new Content($this->content, $this->parent);
	}

	/**
	 * Converts the object to a string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toHtml();
	}

	/**
	 * Returns the content object
	 *
	 * @return \Kirby\Cms\Content
	 */
	public function content()
	{
		return $this->content;
	}

	/**
	 * Controller for the block snippet
	 *
	 * @return array
	 */
	public function controller(): array
	{
		return [
			'block'   => $this,
			'content' => $this->content(),
			// deprecated block data
			'data'    => $this,
			'id'      => $this->id(),
			'prev'    => $this->prev(),
			'next'    => $this->next()
		];
	}

	/**
	 * Converts the block to HTML and then
	 * uses the Str::excerpt method to create
	 * a non-formatted, shortened excerpt from it
	 *
	 * @param mixed ...$args
	 * @return string
	 */
	public function excerpt(...$args)
	{
		return Str::excerpt($this->toHtml(), ...$args);
	}

	/**
	 * Constructs a block object with registering blocks models
	 *
	 * @param array $params
	 * @return static
	 * @throws \Kirby\Exception\InvalidArgumentException
	 * @internal
	 */
	public static function factory(array $params)
	{
		$type = $params['type'] ?? null;

		if (empty($type) === false && $class = (static::$models[$type] ?? null)) {
			$object = new $class($params);

			if ($object instanceof self) {
				return $object;
			}
		}

		// default model for blocks
		if ($class = (static::$models['Kirby\Cms\Block'] ?? null)) {
			$object = new $class($params);

			if ($object instanceof self) {
				return $object;
			}
		}

		return new static($params);
	}

	/**
	 * Checks if the block is empty
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->content()->toArray());
	}

	/**
	 * Checks if the block is hidden
	 * from being rendered in the frontend
	 *
	 * @return bool
	 */
	public function isHidden(): bool
	{
		return $this->isHidden;
	}

	/**
	 * Checks if the block is not empty
	 *
	 * @return bool
	 */
	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Returns the sibling collection that filtered by block status
	 *
	 * @return \Kirby\Cms\Collection
	 */
	protected function siblingsCollection()
	{
		return $this->siblings->filter('isHidden', $this->isHidden());
	}

	/**
	 * Returns the block type
	 *
	 * @return string
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * The result is being sent to the editor
	 * via the API in the panel
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'content'  => $this->content()->toArray(),
			'id'       => $this->id(),
			'isHidden' => $this->isHidden(),
			'type'     => $this->type(),
		];
	}

	/**
	 * Converts the block to html first
	 * and then places that inside a field
	 * object. This can be used further
	 * with all available field methods
	 *
	 * @return \Kirby\Cms\Field
	 */
	public function toField()
	{
		return new Field($this->parent(), $this->id(), $this->toHtml());
	}

	/**
	 * Converts the block to HTML
	 *
	 * @return string
	 */
	public function toHtml(): string
	{
		try {
			$kirby = $this->parent()->kirby();
			return (string)$kirby->snippet('blocks/' . $this->type(), $this->controller(), true);
		} catch (Throwable $e) {
			if ($kirby->option('debug') === true) {
				return '<p>Block error: "' . $e->getMessage() . '" in block type: "' . $this->type() . '"</p>';
			}

			return '';
		}
	}
}
