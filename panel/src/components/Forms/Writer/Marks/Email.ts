import type { MarkSpec } from "prosemirror-model";
import type { Plugin } from "prosemirror-state";
import { isEmail } from "@/helpers/string";
import type { MarkContext } from "../Mark";
import Anchor, { type AnchorAttrs } from "./Anchor";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default class Email extends Anchor {
	protected accepts(href: string | null): boolean {
		return isEmail(href);
	}

	get button() {
		return {
			icon: "email",
			label: window.panel.t("toolbar.button.email")
		};
	}

	get name() {
		return "email";
	}

	protected open(attrs: AnchorAttrs): void {
		window.open("mailto:" + attrs.href);
	}

	pasteRules({ type, utils }: MarkContext): Plugin[] {
		return [
			utils.pasteRule(
				/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/gi,
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
				title: {
					default: undefined
				}
			},
			inclusive: false,
			parseDOM: [
				{
					tag: "a[href^='mailto:']",
					getAttrs: (dom) => {
						const raw = dom.getAttribute("href") ?? "";
						const href = raw.replace(/^mailto:/i, "");

						if (this.accepts(href) === false) {
							return false;
						}

						return {
							href,
							title: dom.getAttribute("title")
						};
					}
				}
			],
			toDOM: (node) => [
				"a",
				{
					...node.attrs,
					href: "mailto:" + node.attrs.href
				},
				0
			]
		};
	}
}
