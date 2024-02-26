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
				const { selection } = this.editor.state;

				// if no text is selected and link mark is not active
				// we insert the link as text
				if (
					selection.empty &&
					this.editor.activeMarks.includes("link") === false
				) {
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
