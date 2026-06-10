import type { MarkSpec } from "prosemirror-model";
import type { Plugin, PluginSpec } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import Mark, { type MarkContext } from "../Mark";

export default class Link extends Mark<{ target: string | null }> {
	get button() {
		return {
			icon: "url",
			label: window.panel.t("toolbar.button.link")
		};
	}

	commands() {
		return {
			link: (event: MouseEvent) => {
				if (event.altKey || event.metaKey) {
					return this.remove();
				}

				this.editor.emit("link", this.editor);
			},
			insertLink: (attrs: { href?: string } = {}) => {
				if (!attrs.href) {
					return;
				}

				const { selection } = this.editor.state!;

				// if no text is selected and link mark is not active
				// we insert the link as text
				// @ts-expect-error fixed once Editor.js is migrated to TS
				if (selection.empty && !this.editor.activeMarks.includes("link")) {
					this.editor.insertText(attrs.href, true);
				}

				return this.update(attrs);
			},
			removeLink: () => {
				return this.remove();
			},
			toggleLink: (attrs: { href?: string } = {}) => {
				if (attrs.href) {
					this.editor.command("insertLink", attrs);
				} else {
					this.editor.command("removeLink");
				}
			}
		};
	}

	get defaults() {
		return {
			target: null
		};
	}

	get name() {
		return "link";
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

	plugins(): PluginSpec<null>[] {
		return [
			{
				props: {
					handleClick: (_view: EditorView, _pos: number, event: MouseEvent) => {
						// @ts-expect-error fixed once Editor.js is migrated to TS
						const attrs = this.editor.getMarkAttrs("link");

						if (
							attrs.href &&
							event.altKey === true &&
							event.target instanceof HTMLAnchorElement
						) {
							event.stopPropagation();
							window.open(attrs.href, attrs.target);
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
				target: {
					default: this.options.target
				},
				title: {
					default: null
				}
			},
			inclusive: false,
			parseDOM: [
				{
					tag: "a[href]:not([href^='mailto:'])",
					getAttrs: (dom) => ({
						href: dom.getAttribute("href"),
						target: dom.getAttribute("target"),
						title: dom.getAttribute("title")
					})
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
