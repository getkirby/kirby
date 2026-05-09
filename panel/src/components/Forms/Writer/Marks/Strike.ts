import type { InputRule } from "prosemirror-inputrules";
import type { MarkSpec } from "prosemirror-model";
import type { Plugin } from "prosemirror-state";
import Mark, { type MarkContext } from "../Mark";

export default class Strike extends Mark {
	get button() {
		return {
			icon: "strikethrough",
			label: window.panel.t("toolbar.button.strike")
		};
	}

	commands() {
		return () => this.toggle();
	}

	inputRules({ type, utils }: MarkContext): InputRule[] {
		return [utils.markInputRule(/~([^~]+)~$/, type)];
	}

	keys() {
		return {
			"Mod-d": () => this.toggle()
		};
	}

	get name() {
		return "strike";
	}

	pasteRules({ type, utils }: MarkContext): Plugin[] {
		return [utils.markPasteRule(/~([^~]+)~/g, type)];
	}

	get schema(): MarkSpec {
		return {
			parseDOM: [
				{ tag: "s" },
				{ tag: "del" },
				{ tag: "strike" },
				{
					style: "text-decoration",
					getAttrs: (value) => value === "line-through" && null
				}
			],
			toDOM: () => ["s", 0]
		};
	}
}
