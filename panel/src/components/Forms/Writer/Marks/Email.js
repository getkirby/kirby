import Mark from "../Mark";
import { isEmail } from "@/helpers/string";

export default class Email extends Mark {
	get button() {
		return {
			icon: "email",
			label: window.panel.$t("toolbar.button.email")
		};
	}

	commands() {
		return {
			email: (event) => {
				if (event.altKey || event.metaKey) {
					return this.remove();
				}

				this.editor.emit("email", this.editor);
			},
			insertEmail: (attrs = {}) => {
				const { selection } = this.editor.state;

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
			toggleEmail: (attrs = {}) => {
				if (attrs.href?.length > 0) {
					this.editor.command("insertEmail", attrs);
				} else {
					this.editor.command("removeEmail");
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
		return "email";
	}

	pasteRules({ type, utils }) {
		return [
			utils.pasteRule(
				/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/gi,
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

	get schema() {
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
