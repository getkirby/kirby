import type { MarkSpec } from "prosemirror-model";
import type { Plugin } from "prosemirror-state";
import { hasDangerousScheme } from "@/helpers/url.js";
import type { MarkContext } from "../Mark";
import Anchor, { type AnchorAttrs } from "./Anchor";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default class Link extends Anchor<{ target?: string }> {
	// reject dangerous schemes (javascript:, vbscript:, data: etc.)
	protected accepts(href: string | null): boolean {
		return hasDangerousScheme(href) === false;
	}

	get button() {
		return {
			icon: "url",
			label: window.panel.t("toolbar.button.link")
		};
	}

	get defaults() {
		return {
			target: undefined
		};
	}

	get name() {
		return "link";
	}

	protected open(attrs: AnchorAttrs): void {
		window.open(attrs.href, attrs.target);
	}

	pasteRules({ type, utils }: MarkContext): Plugin[] {
		return [
			utils.pasteRule(
				/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+.~#?&//=,]*)/gi,
				type,
				(url) => ({ href: url })
			)
		];
	}

	get schema(): MarkSpec {
		return {
			attrs: {
				href: {
					default: ""
				},
				target: {
					default: this.options.target
				},
				title: {
					default: undefined
				}
			},
			inclusive: false,
			parseDOM: [
				{
					tag: "a[href]:not([href^='mailto:'])",
					getAttrs: (dom) => {
						const href = dom.getAttribute("href");

						if (this.accepts(href) === false) {
							return false;
						}

						return {
							href,
							target: dom.getAttribute("target"),
							title: dom.getAttribute("title")
						};
					}
				}
			],
			toDOM: (node) => [
				"a",
				{
					...node.attrs
				},
				0
			]
		};
	}
}
