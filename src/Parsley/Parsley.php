<?php

namespace Kirby\Parsley;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Kirby\Parsley\Schema\Plain;
use Kirby\Toolkit\Dom;

/**
 * HTML parser to extract the best possible blocks
 * from any kind of HTML document
 *
 * @since 3.5.0
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Parsley
{
	protected array $blocks = [];
	protected DOMDocument $doc;
	protected Dom $dom;
	protected array $inline = [];
	protected array $marks = [];
	protected array $nodes = [];
	protected Schema $schema;
	protected array $skip = [];

	public static bool $useXmlExtension = true;

	public function __construct(string $html, Schema|null $schema = null)
	{
		// fail gracefully if the XML extension is not installed
		// or should be skipped
		if ($this->useXmlExtension() === false) {
			$this->blocks[] = [
				'type'    => 'markdown',
				'content' => ['text' => $html]
			];
			return;
		}

		if (!preg_match('/<body|head*.?>/', $html)) {
			$html = '<div>' . $html . '</div>';
		}

		$this->dom    = new Dom($html);
		$this->doc    = $this->dom->document();
		$this->schema = $schema ?? new Plain();
		$this->skip   = $this->schema->skip();
		$this->marks  = $this->schema->marks();
		$this->inline = [];

		// load all allowed nodes from the schema
		$this->createNodeRules($this->schema->nodes());

		// start parsing at the top level and go through
		// all children of the document
		foreach ($this->doc->childNodes as $childNode) {
			$this->parseNode($childNode);
		}

		// needs to be called at last to fetch remaining
		// inline elements after parsing has ended
		$this->endInlineBlock();
	}

	/**
	 * Returns all detected blocks
	 */
	public function blocks(): array
	{
		return $this->blocks;
	}

	/**
	 * Load all node rules from the schema
	 */
	public function createNodeRules(array $nodes): array
	{
		foreach ($nodes as $node) {
			$this->nodes[$node['tag']] = $node;
		}

		return $this->nodes;
	}

	/**
	 * Checks if the given element contains
	 * any other block level elements
	 */
	public function containsBlock(DOMNode $element): bool
	{
		if ($element->hasChildNodes() === false) {
			return false;
		}

		foreach ($element->childNodes as $childNode) {
			if (
				$this->isBlock($childNode) === true ||
				$this->containsBlock($childNode)
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Takes all inline elements in the inline cache
	 * and combines them in a final block. The block
	 * will either be merged with the previous block
	 * if the type matches, or will be appended.
	 *
	 * The inline cache will be reset afterwards
	 */
	public function endInlineBlock(): void
	{
		if (empty($this->inline) === true) {
			return;
		}

		$html = [];

		foreach ($this->inline as $inline) {
			$node   = new Inline($inline, $this->marks);
			$html[] = $node->innerHTML();
		}

		$innerHTML = implode(' ', $html);

		if ($fallback = $this->fallback($innerHTML)) {
			$this->mergeOrAppend($fallback);
		}

		$this->inline = [];
	}

	/**
	 * Creates a fallback block type for the given
	 * element. The element can either be a element object
	 * or a simple HTML/plain text string
	 */
	public function fallback(Element|string $element): array|null
	{
		if ($fallback = $this->schema->fallback($element)) {
			return $fallback;
		}

		return null;
	}

	/**
	 * Checks if the given DOMNode is a block element
	 */
	public function isBlock(DOMNode $element): bool
	{
		if ($element instanceof DOMElement) {
			return array_key_exists($element->tagName, $this->nodes) === true;
		}

		return false;
	}

	/**
	 * Checks if the given DOMNode is an inline element
	 */
	public function isInline(DOMNode $element): bool
	{
		if ($element instanceof DOMText) {
			return true;
		}

		if ($element instanceof DOMElement) {
			// all spans will be treated as inline elements
			if ($element->tagName === 'span') {
				return true;
			}

			if ($this->containsBlock($element) === true) {
				return false;
			}

			if ($element->tagName === 'p') {
				return false;
			}

			$marks = array_column($this->marks, 'tag');
			return in_array($element->tagName, $marks);
		}

		return false;
	}

	public function mergeOrAppend(array $block): void
	{
		$lastIndex = count($this->blocks) - 1;
		$lastItem  = $this->blocks[$lastIndex] ?? null;

		// merge with previous block
		if (
			$block['type'] === 'text' &&
			$lastItem &&
			$lastItem['type'] === 'text'
		) {
			$this->blocks[$lastIndex]['content']['text'] .= ' ' . $block['content']['text'];

		// append
		} else {
			$this->blocks[] = $block;
		}
	}

	/**
	 * Parses the given DOM node and tries to
	 * convert it to a block or a list of blocks
	 */
	public function parseNode(DOMNode $element): bool
	{
		$skip = ['DOMComment', 'DOMDocumentType'];

		// unwanted element types
		if (in_array(get_class($element), $skip) === true) {
			return false;
		}

		// inline context
		if ($this->isInline($element) === true) {
			$this->inline[] = $element;
			return true;
		}

		$this->endInlineBlock();

		// known block nodes
		if ($this->isBlock($element) === true) {
			/**
			 * @var DOMElement $element
			 */
			if ($parser = ($this->nodes[$element->tagName]['parse'] ?? null)) {
				if ($result = $parser(new Element($element, $this->marks))) {
					$this->blocks[] = $result;
				}
			}
			return true;
		}

		// has only unknown children (div, etc.)
		if ($this->containsBlock($element) === false) {
			/**
			 * @var DOMElement $element
			 */
			if (in_array($element->tagName, $this->skip) === true) {
				return false;
			}

			$wrappers = [
				'body',
				'head',
				'html',
			];

			// wrapper elements should never be converted
			// to a simple fallback block. Their children
			// have to be parsed individually.
			if (in_array($element->tagName, $wrappers) === false) {
				$node = new Element($element, $this->marks);

				if ($block = $this->fallback($node)) {
					$this->mergeOrAppend($block);
				}

				return true;
			}
		}

		// parse all children
		foreach ($element->childNodes as $childNode) {
			$this->parseNode($childNode);
		}

		return true;
	}

	public function useXmlExtension(): bool
	{
		if (static::$useXmlExtension !== true) {
			return false;
		}

		return Dom::isSupported();
	}
}
