<?php

namespace Kirby\Cms;

use Kirby\Content\Content;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use Stringable;
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
 *
 * @extends \Kirby\Cms\Item<\Kirby\Cms\Blocks>
 */
class Block extends Item implements Stringable
{
	use HasMethods;
	use HasModels;

	public const ITEMS_CLASS = Blocks::class;

	protected Content $content;
	protected bool $isHidden;
	protected string $type;

	/**
	 * Proxy for content fields
	 */
	public function __call(string $method, array $args = []): mixed
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
			throw new InvalidArgumentException(
				message: 'The block type is missing'
			);
		}

		// make sure the content is always defined as array to keep
		// at least a bit of backward compatibility with older fields
		if (is_array($params['content'] ?? null) === false) {
			$params['content'] = [];
		}

		$this->isHidden = $params['isHidden'] ?? false;
		$this->type     = $params['type'];

		// create the content object
		$this->content = new Content($params['content'], $this->parent);
	}

	/**
	 * Converts the object to a string
	 */
	public function __toString(): string
	{
		return $this->toHtml();
	}

	/**
	 * Returns the content object
	 */
	public function content(): Content
	{
		return $this->content;
	}

	/**
	 * Controller for the block snippet
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
	 */
	public function excerpt(mixed ...$args): string
	{
		return Str::excerpt($this->toHtml(), ...$args);
	}

	/**
	 * Constructs a block object with registering blocks models
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public static function factory(array $params): static
	{
		return static::model($params['type'] ?? 'default', $params);
	}

	/**
	 * Checks if the block is empty
	 */
	public function isEmpty(): bool
	{
		return empty($this->content()->toArray());
	}

	/**
	 * Checks if the block is hidden
	 * from being rendered in the frontend
	 */
	public function isHidden(): bool
	{
		return $this->isHidden;
	}

	/**
	 * Checks if the block is not empty
	 */
	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Returns the sibling collection that filtered by block status
	 */
	protected function siblingsCollection(): Blocks
	{
		return $this->siblings->filter('isHidden', $this->isHidden());
	}

	/**
	 * Returns the block type
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * The result is being sent to the editor
	 * via the API in the panel
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
	 */
	public function toField(): Field
	{
		return new Field($this->parent(), $this->id(), $this->toHtml());
	}

	/**
	 * Converts the block to HTML
	 */
	public function toHtml(): string
	{
		try {
			$kirby = $this->parent()->kirby();
			return (string)$kirby->snippet(
				'blocks/' . $this->type(),
				$this->controller(),
				true
			);
		} catch (Throwable $e) {
			if ($kirby->option('debug') === true) {
				return '<p>Block error: "' . $e->getMessage() . '" in block type: "' . $this->type() . '"</p>';
			}

			return '';
		}
	}
}
