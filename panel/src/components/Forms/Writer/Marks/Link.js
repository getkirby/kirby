import { hasDangerousScheme } from "@/helpers/url.js";
import Mark from "../Mark";

export default class Link extends Mark {
	get button() {
		return {
			icon: "url",
			label: window.panel.$t("toolbar.button.link")
		};
	}

	commands() {
		return {
			link: (event) => {
				if (event.altKey || event.metaKey) {
					return this.remove();
				}

				this.editor.emit("link", this.editor);
			},
			insertLink: (attrs = {}) => {
				const hasLinkMark = this.editor.activeMarks.includes("link");

				// reject dangerous schemes (javascript:, vbscript:, data: etc.)
				// at insert time so they never enter the document state;
				// if a link mark is already active, remove it entirely
				if (hasDangerousScheme(attrs.href) === true) {
					if (hasLinkMark === true) {
						return this.remove();
					}

					return;
				}

				const { selection } = this.editor.state;

				// if no text is selected and link mark is not active
				// we insert the link as text
				if (selection.empty && hasLinkMark === false) {
					this.editor.insertText(attrs.href, true);
				}

				if (attrs.href) {
					return this.update(attrs);
				}
			},
			removeLink: () => {
				return this.remove();
			},
			toggleLink: (attrs = {}) => {
				if (attrs.href?.length > 0) {
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

	pasteRules({ type, utils }) {
		return [
			utils.pasteRule(
				/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+.~#?&//=,]*)/gi,
				type,
				(url) => ({ href: url })
			)
		];
	}

	plugins() {
		return [
			{
				props: {
					handleClick: (view, pos, event) => {
						const attrs = this.editor.getMarkAttrs("link");

						if (
							attrs.href &&
							event.altKey === true &&
							event.target instanceof HTMLAnchorElement &&
							hasDangerousScheme(attrs.href) === false
						) {
							event.stopPropagation();
							window.open(attrs.href, attrs.target);
						}
					}
				}
			}
		];
	}

	get schema() {
		return {
			attrs: {
				href: {
					default: null
				},
				target: {
					default: null
				},
				title: {
					default: null
				}
			},
			inclusive: false,
			parseDOM: [
				{
					tag: "a[href]:not([href^='mailto:'])",
					getAttrs: (dom) => {
						const href = dom.getAttribute("href");

						// reject the link entirely if the href uses a
						// dangerous scheme (javascript:, vbscript:, data: etc.)
						if (hasDangerousScheme(href) === true) {
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
