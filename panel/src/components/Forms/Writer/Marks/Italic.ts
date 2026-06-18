import type { InputRule } from "prosemirror-inputrules";
import type { MarkSpec } from "prosemirror-model";
import type { Plugin } from "prosemirror-state";
import Mark, { type MarkContext } from "../Mark";

export default class Italic extends Mark {
	get button() {
		return {
			icon: "italic",
			label: window.panel.t("toolbar.button.italic")
		};
	}

	commands() {
		return () => this.toggle();
	}

	inputRules({ type, utils }: MarkContext): InputRule[] {
		return [
			utils.markInputRule(
				/(?:^|\s)(\*([^*\s](?:(?:[^*]|\*(?![*\s]))*[^*\s])?)\*)$/,
				type
			),
			utils.markInputRule(
				/(?:^|\s)(_([^_\s](?:(?:[^_]|_(?![_\s]))*[^_\s])?)_)$/,
				type
			)
		];
	}

	keys() {
		return {
			"Mod-i": () => this.toggle()
		};
	}

	get name() {
		return "italic";
	}

	pasteRules({ type, utils }: MarkContext): Plugin[] {
		return [
			utils.markPasteRule(
				/(?<!\S)\*([^*\s](?:(?:[^*]|\*(?![*\s]))*[^*\s])?)\*(?!\S)/g,
				type
			),
			utils.markPasteRule(
				/(?<!\S)_([^_\s](?:(?:[^_]|_(?![_\s]))*[^_\s])?)_(?!\S)/g,
				type
			)
		];
	}

	get schema(): MarkSpec {
		return {
			parseDOM: [
				{ tag: "i" },
				{ tag: "em" },
				{
					style: "font-style",
					getAttrs: (value) => value === "italic" && null
				}
			],
			toDOM: () => ["em", 0]
		};
	}
}
