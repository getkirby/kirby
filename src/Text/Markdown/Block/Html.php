<?php

namespace Kirby\Text\Markdown\Block;

use DOMElement;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html as HtmlNode;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\HtmlElements;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Toolkit\Dom;
use Kirby\Toolkit\Html as ToolkitHtml;

/**
 * Raw HTML block
 *
 * A line that opens with a non-text-level HTML tag.
 * Re-parses the content of any element marked `markdown="1"`.
 *
 * @example
 * <div markdown="1">
 * This is *true* markdown text.
 * </div>
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 *
 * @todo `HtmlElements::TEXT_LEVEL` and `::ATTRIBUTE_REGEX` still live in the
 *       parser: `Toolkit\Html::$inlineList` omits inline tags (`del`, `ins`,
 *       `big`, `font`, …) the parser needs for CommonMark parity, and there is
 *       no `Toolkit\Html` attribute pattern to reuse.
 */
class Html extends Block
{
	protected const PATTERN = '/^<(\w[\w-]*)(?:[ ]*' . HtmlElements::ATTRIBUTE_REGEX . ')*[ ]*(\/)?>/';

	public static function markers(): array
	{
		return ['<'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		if ($this->parser->safe === true) {
			return false;
		}

		$matches = $line->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$name = $matches[1];

		if (in_array(strtolower($name), HtmlElements::TEXT_LEVEL) === true) {
			return false;
		}

		$html      = $line->text();
		$closed    = false;
		$void      = false;
		$remainder = $line->slice(strlen($matches[0]));

		if (trim($remainder) === '') {
			if (
				isset($matches[2]) === true ||
				in_array($name, ToolkitHtml::$voidList) === true
			) {
				$closed = true;
				$void   = true;
			}
		} else {
			if (
				isset($matches[2]) === true ||
				in_array($name, ToolkitHtml::$voidList) === true
			) {
				return false;
			}

			if (preg_match('/<\/' . $name . '>[ ]*$/i', $remainder) === 1) {
				$closed = true;
			}
		}

		$line->next();

		$depth       = 0;
		$interrupted = 0;

		// $name is fixed for the whole block, so build the open/close tag
		// patterns once instead of rebuilding them on every line read
		$open  = '/^<' . $name . '(?:[ ]*' . HtmlElements::ATTRIBUTE_REGEX . ')*[ ]*>/i';
		$close = '/<\/' . $name . '>[ ]*$/i';

		// read until the matching closing tag at depth 0 (not a blank line)
		while ($closed === false && $line->valid() === true) {
			if ($line->isBlank() === true) {
				$interrupted++;
				$line->next();
				continue;
			}

			if ($line->matches($open) === true) {
				$depth++;
			}

			if ($line->matches($close) === true) {
				if ($depth > 0) {
					$depth--;
				} else {
					$closed = true;
				}
			}

			if ($interrupted > 0) {
				$html       .= "\n";
				$interrupted = 0;
			}

			$html .= "\n" . $line->body();
			$line->next();
		}

		// resolve any `markdown="1"` content
		if ($void === false) {
			$html = $this->tag($html);
		}

		return new HtmlNode($html, break: true);
	}

	/**
	 * Recursively walks the block's HTML and re-parses
	 * the content of any element carrying `markdown="1"`
	 * as Markdown, leaving the rest untouched.
	 */
	protected function tag(string $html): string
	{
		// the DOM round-trip exists only to re-parse `markdown="1"` content;
		// a block without a `markdown=` attribute is passed through verbatim
		// instead of being normalized (and possibly mangled) by DOMDocument
		if (str_contains($html, 'markdown=') === false) {
			return $html;
		}

		try {
			$dom = new Dom($html);
		} catch (InvalidArgumentException) { // @codeCoverageIgnore
			return $html; // @codeCoverageIgnore
		}

		$document = $dom->document();
		$root     = $dom->body()?->firstChild;

		// defensive: the block did not parse into a single root element
		// (e.g. empty or malformed markup, or a self-tag like `<html>` that
		// libxml relocates); leave it untouched.
		if ($root instanceof DOMElement === false) {
			return $html; // @codeCoverageIgnore
		}

		$text = '';

		if ($root->getAttribute('markdown') === '1') {
			$text = "\n" . $this->parser->parse($dom->innerMarkup($root)) . "\n";

			$root->removeAttribute('markdown');
		} else {
			foreach ($root->childNodes as $node) {
				$nodeMarkup = $document->saveHTML($node);

				if (
					$node instanceof DOMElement &&
					in_array($node->nodeName, HtmlElements::TEXT_LEVEL, true) === false
				) {
					$text .= $this->tag($nodeMarkup);
				} else {
					$text .= $nodeMarkup;
				}
			}
		}

		// serialize the wrapper with a placeholder, then swap the
		// processed markup back in so it does not get HTML-encoded
		$root->nodeValue = 'placeholder\x1A';

		$html = $document->saveHTML($root);
		$html = str_replace('placeholder\x1A', $text, $html);

		return $html;
	}
}
