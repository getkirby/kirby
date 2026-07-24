<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Document;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\HardBreak;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\SoftBreak;
use Kirby\Text\Markdown\AST\Text;

/**
 * Walks a markdown AST and produces HTML.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Renderer
{
	/**
	 * URL schemes permitted in safe mode
	 * @var list<string>
	 */
	protected const array SAFE_URL_SCHEMES = [
		'http://',
		'https://',
		'ftp://',
		'ftps://',
		'mailto:',
		'tel:',
		'data:image/png;base64,',
		'data:image/gif;base64,',
		'data:image/jpeg;base64,',
		'irc:',
		'ircs:',
		'git:',
		'ssh:',
		'news:',
		'steam:',
	];

	public function __construct(
		protected bool $safe = false
	) {
	}

	protected static function escape(
		string $text,
		bool $quotes = false
	): string {
		return htmlspecialchars($text, $quotes ? ENT_COMPAT : ENT_QUOTES, 'UTF-8');
	}

	/**
	 * @param array<string, string|null> $attrs
	 * @return array<string, string|null>
	 */
	protected function filterUnsafeUrlInAttribute(
		array $attrs,
		string $attr
	): array {
		foreach (static::SAFE_URL_SCHEMES as $scheme) {
			if (static::stringAtStart($attrs[$attr], $scheme) === true) {
				return $attrs;
			}
		}

		$attrs[$attr] = str_replace(':', '%3A', $attrs[$attr]);

		return $attrs;
	}

	/**
	 * Renders a single node to HTML.
	 */
	public function render(Node $node): string
	{
		// ordered by frequency: text and element nodes make up
		// the bulk, the single Document root is last
		return match (true) {
			$node instanceof Text      => static::escape($node->text, true),
			$node instanceof Element   => $this->renderElement($node),
			$node instanceof SoftBreak => "\n",
			$node instanceof HardBreak => "<br />\n",
			$node instanceof Html      => $this->renderHtml($node),
			$node instanceof Document  => $this->renderNodes($node->children)
		};
	}

	protected function renderElement(Element $element): string
	{
		$name  = $element->name;
		$attrs = $element->attributes;

		if ($this->safe === true) {
			$attrs = $this->sanitizeAttributes($name, $attrs);
		}

		$hasName = $name !== null;
		$markup  = '';

		if ($hasName === true) {
			$markup .= '<' . $name;

			foreach ($attrs as $attribute => $value) {
				if ($value === null) {
					continue;
				}

				$markup .= " $attribute=\"" . static::escape($value) . '"';
			}
		}

		$hasContent = $element->children !== null;

		// an empty list item renders without an inner break: `<li></li>`
		if ($name === 'li' && $element->children === []) {
			return $markup . '></li>';
		}

		if ($hasContent === true) {
			$markup .= $hasName ? '>' : '';

			if ($element->multiline === true) {
				$markup .= $this->renderNodes($element->children, $element->block);
			} else {
				foreach ($element->children as $child) {
					$markup .= $child instanceof Text
						? static::escape($child->text, true)
						: $this->render($child);
				}
			}

			$markup .= $hasName ? '</' . $name . '>' : '';
		} elseif ($hasName === true) {
			$markup .= ' />';
		}

		return $markup;
	}

	protected function renderHtml(Html $node): string
	{
		if ($this->safe === false || $node->trusted === true) {
			return $node->html;
		}

		return static::escape($node->html, true);
	}

	/**
	 * Renders a list of sibling nodes.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 */
	public function renderNodes(array $nodes, bool $block = false): string
	{
		$markup   = '';
		$previous = null; // the previous node's break flag, `null` before the first

		foreach ($nodes as $node) {
			$break = $node->hasBreak();

			// the first node breaks off the parent tag when it is block-level;
			// block-level siblings always sit on their own line (a tight list
			// item's unwrapped text still precedes a child block on its own
			// line); inline siblings break only when both do
			$lead = match (true) {
				$previous === null => $break,
				$block === true    => true,
				default            => $previous && $break
			};

			if ($lead === true) {
				$markup .= "\n";
			}

			// text is the node plurality; escape it inline to skip a render()
			// frame on the hot per-node path, delegate the rest
			$markup  .= $node instanceof Text
				? static::escape($node->text, true)
				: $this->render($node);
			$previous = $break;
		}

		// a trailing break when the last node breaks; an empty container
		// (e.g. `<blockquote></blockquote>`) keeps its single inner break
		return $markup . (($previous ?? true) ? "\n" : '');
	}

	/**
	 * @param array<string, string|null> $attrs
	 * @return array<string, string|null>
	 */
	protected function sanitizeAttributes(
		string|null $name,
		array $attrs
	): array {
		static $safe     = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
		static $tag2Attr = [
			'a'   => 'href',
			'img' => 'src',
		];

		if ($name === null) {
			return [];
		}

		if (isset($tag2Attr[$name]) === true) {
			$attrs = $this->filterUnsafeUrlInAttribute($attrs, $tag2Attr[$name]);
		}

		if ($attrs !== []) {
			foreach (array_keys($attrs) as $attr) {
				if (!preg_match($safe, $attr)) {
					unset($attrs[$attr]);
				} elseif (static::stringAtStart($attr, 'on') === true) {
					unset($attrs[$attr]);
				}
			}
		}

		return $attrs;
	}

	protected static function stringAtStart(
		string $string,
		string $needle
	): bool {
		return strncasecmp($string, $needle, strlen($needle)) === 0;
	}
}
