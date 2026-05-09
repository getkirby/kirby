import type { MarkSpec } from "prosemirror-model";
import type { Plugin, PluginSpec } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import Mark, { type MarkContext } from "../Mark";

export default class Email extends Mark {
	get button() {
		return {
			icon: "email",
			label: window.panel.t("toolbar.button.email")
		};
	}

	commands() {
		return {
			email: (event: MouseEvent) => {
				if (event.altKey || event.metaKey) {
					return this.remove();
				}

				this.editor.emit("email", this.editor);
			},
			insertEmail: (attrs: { href?: string } = {}) => {
				const { selection } = this.editor.state!;

				// if no text is selected, we insert the link as text
				if (selection.empty) {
					this.editor.insertText(attrs.href, true);
				}

				if (attrs.href) {
					return this.update(attrs);
				}
			},
			removeEmail: () => {
				return this.remove();
			},
			toggleEmail: (attrs: { href?: string } = {}) => {
				if (attrs.href) {
					this.editor.command("insertEmail", attrs);
				} else {
					this.editor.command("removeEmail");
				}
			}
		};
	}

	get name() {
		return "email";
	}

	pasteRules({ type, utils }: MarkContext): Plugin[] {
		return [
			utils.pasteRule(/[\w-.]+@([\w-]+\.)+[\w-]{2,4}/gi, type, (url) => ({
				href: url
			}))
		];
	}

	plugins(): PluginSpec<null>[] {
		return [
			{
				props: {
					handleClick: (_view: EditorView, _pos: number, event: MouseEvent) => {
						// @ts-expect-error fixed once Editor.js is migrated to TS
						const attrs = this.editor.getMarkAttrs("email");

						if (
							attrs.href &&
							event.altKey === true &&
							event.target instanceof HTMLAnchorElement
						) {
							event.stopPropagation();
							window.open("mailto:" + attrs.href);
						}
					}
				}
			}
		];
	}

	get schema(): MarkSpec {
		return {
			attrs: {
				href: {
					default: null
				},
				title: {
					default: null
				}
			},
			inclusive: false,
			parseDOM: [
				{
					tag: "a[href^='mailto:']",
					getAttrs: (dom) => ({
						href: dom.getAttribute("href")!.replace("mailto:", ""),
						title: dom.getAttribute("title")
					})
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
