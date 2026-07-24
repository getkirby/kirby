<?php

namespace Kirby\Text\Markdown\Block;

use DOMElement;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html as HtmlNode;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Toolkit\Dom;

/**
 * Raw HTML block
 *
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
 */
class Html extends LeafBlock
{
	/**
	 * Pattern matching a single HTML attribute,
	 * used to recognize complete tags and `markdown=` elements.
	 */
	protected const string ATTRIBUTE = '[a-zA-Z_:][\w:.-]*+(?:\s*+=\s*+(?:[^"\'=<>`\s]+|"[^"]*+"|\'[^\']*+\'))?+';

	/**
	 * Tags whose content is verbatim; the block ends on their
	 * closing tag (CommonMark HTML block type 1).
	 */
	protected const string RAW = '/^<(?:script|pre|style|textarea)(?:\s|>|$)/i';

	/**
	 * Block-level tag names that open an HTML block ending at the
	 * next blank line (CommonMark HTML block type 6).
	 */
	protected const string BLOCK = '/^<\/?(?:address|article|aside|base|basefont|blockquote|body|caption|center|col|colgroup|dd|details|dialog|dir|div|dl|dt|fieldset|figcaption|figure|footer|form|frame|frameset|h[1-6]|head|header|hr|html|iframe|legend|li|link|main|menu|menuitem|nav|noframes|ol|optgroup|option|p|param|section|summary|table|tbody|td|tfoot|th|thead|title|tr|track|ul)(?:\s|\/?>|$)/i';

	/**
	 * A complete open or closing tag filling the whole line
	 * (CommonMark HTML block type 7).
	 */
	protected const string TAG = '/^(?:<[a-zA-Z][a-zA-Z0-9-]*+(?:\s+' . self::ATTRIBUTE . ')*+\s*+\/?>|<\/[a-zA-Z][a-zA-Z0-9-]*+\s*+>)\s*+$/';

	public static function markers(): array
	{
		return ['<'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		// raw HTML is not emitted in safe mode; an HTML block may be
		// indented up to three spaces (four would be indented code)
		if ($this->parser->safe === true || $line->indent() >= 4) {
			return false;
		}

		// may not interrupt a paragraph that is still being
		// continued; a preceding blank line has already ended it
		$continuation = $paragraph !== null && $line->isBlank(offset: -1) === false;

		[$end, $type] = $this->start($line->text(), $continuation);

		if ($type === 0) {
			return false;
		}

		// Kirby/ParsedownExtra extension: an element carrying a
		// `markdown=` attribute captures its whole content across blank
		// lines so `markdown="1"` can re-parse it
		if (
			($type === 6 || $type === 7) &&
			preg_match('/^<(\w[\w-]*)[^>]*markdown=/i', $line->text(), $tag) === 1
		) {
			$element = $this->element($line, $tag[1]);
			$tag     = $this->tag($element);
			return new HtmlNode($tag, break: true);
		}

		// the opening line is kept with its original indentation
		$html = $line->body();

		// types 1–5 may already close on their opening line
		if ($end !== null && preg_match($end, $line->text()) === 1) {
			$line->next();
			return new HtmlNode($this->tag($html), break: true);
		}

		$line->next();

		while ($line->valid() === true) {
			// types 6–7 end at a blank line, which is not consumed
			if ($end === null && $line->isBlank() === true) {
				break;
			}

			$html .= "\n" . $line->body();

			// types 1–5 end at (and include) the line with the closer
			if ($end !== null && preg_match($end, $line->text()) === 1) {
				$line->next();
				break;
			}

			$line->next();
		}

		$tag = $this->tag($html);

		return new HtmlNode($tag, break: true);
	}

	/**
	 * Captures a full HTML element by matching its closing tag at
	 * depth zero, keeping interior blank lines. Used only for the
	 * `markdown=` extension, where the whole element is re-parsed.
	 */
	protected function element(Line $line, string $tag): string
	{
		$html  = $line->body();
		$open  = '/^<' . $tag . '(?:[ ]*' . self::ATTRIBUTE . ')*+[ ]*>/i';
		$close = '/<\/' . $tag . '>[ ]*$/i';

		// the element may already close on its opening line
		if (preg_match($close, $line->text()) === 1) {
			$line->next();
			return $html;
		}

		$line->next();

		$depth       = 0;
		$interrupted = 0;

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$interrupted++;
				$line->next();
				continue;
			}

			if ($line->matches($open) === true) {
				$depth++;
			}

			$closed = false;

			if ($line->matches($close) === true) {
				if ($depth > 0) {
					$depth--;
				} else {
					$closed = true;
				}
			}

			if ($interrupted > 0) {
				$html       .= str_repeat("\n", $interrupted);
				$interrupted = 0;
			}

			$html .= "\n" . $line->body();
			$line->next();

			if ($closed === true) {
				break;
			}
		}

		return $html;
	}

	/**
	 * Classifies the HTML block opened by the given line, returning
	 * `[end, type]`: the end-condition pattern and the CommonMark
	 * block type, `0` when the line opens none.
	 *
	 * @return array{string|null, int}
	 */
	protected function start(string $text, bool $continuation): array
	{
		return match (true) {
			preg_match(self::RAW, $text) === 1       => ['/<\/(?:script|pre|style|textarea)>/i', 1],
			str_starts_with($text, '<!--')           => ['/-->/', 2],
			str_starts_with($text, '<?')             => ['/\?>/', 3],
			preg_match('/^<![a-zA-Z]/', $text) === 1 => ['/>/', 4],
			str_starts_with($text, '<![CDATA[')      => ['/\]\]>/', 5],
			preg_match(self::BLOCK, $text) === 1     => [null, 6],
			// type 7 may not interrupt a paragraph continuation
			$continuation === false &&
				preg_match(self::TAG, $text) === 1   => [null, 7],
			default                                  => [null, 0]
		};
	}

	/**
	 * Re-parses the content of every element carrying `markdown="1"`
	 * as Markdown (via a DOM round-trip), leaving the rest of the
	 * HTML untouched.
	 */
	protected function tag(string $html): string
	{
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

		// re-parse the content of every outermost `markdown="1"` element (a
		// nested one is swallowed by its ancestor's re-parse). The parsed
		// markup is swapped back in through a placeholder so the DOM
		// serializer does not HTML-encode it.
		$swaps = [];
		$nonce = bin2hex(random_bytes(8));

		foreach ($dom->query('//*[@markdown="1"][not(ancestor::*[@markdown="1"])]') as $element) {
			if ($element instanceof DOMElement === false) {
				continue; // @codeCoverageIgnore
			}

			$placeholder         = '{{' . $nonce . count($swaps) . '}}';
			$swaps[$placeholder] = "\n" . $this->parser->parse($dom->innerMarkup($element)) . "\n";

			$element->removeAttribute('markdown');
			$element->nodeValue = $placeholder;
		}

		return strtr($document->saveHTML($root), $swaps);
	}
}
