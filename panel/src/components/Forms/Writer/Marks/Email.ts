import type { MarkSpec } from "prosemirror-model";
import type { Plugin, PluginSpec } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import { isEmail } from "@/helpers/string";
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
				if (!attrs.href) {
					return;
				}

				const hasEmailMark = this.editor.activeMarks.includes("email");

				// reject anything that doesn't look like an email so it
				// never enters the document state; if an email mark is
				// already active, remove it entirely
				if (isEmail(attrs.href) === false) {
					if (hasEmailMark === true) {
						return this.remove();
					}

					return;
				}

				const { selection } = this.editor.state!;

				// if no text is selected and email mark is not active
				// we insert the email as text
				if (selection.empty && hasEmailMark === false) {
					this.editor.insertText(attrs.href, true);
				}

				return this.update(attrs);
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
			utils.pasteRule(
				/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/gi,
				type,
				(url) => ({ href: url })
			)
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
							event.target instanceof HTMLAnchorElement &&
							isEmail(attrs.href) === true
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
					getAttrs: (dom) => {
						const raw = dom.getAttribute("href") ?? "";
						const href = raw.replace(/^mailto:/i, "");

						if (isEmail(href) === false) {
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
