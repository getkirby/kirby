import type { InputRule } from "prosemirror-inputrules";
import type { MarkSpec } from "prosemirror-model";
import type { Plugin } from "prosemirror-state";
import Mark, { type MarkContext } from "../Mark";

export default class Code extends Mark {
	get button() {
		return {
			icon: "code",
			label: window.panel.t("toolbar.button.code")
		};
	}

	commands() {
		return () => this.toggle();
	}

	inputRules({ type, utils }: MarkContext): InputRule[] {
		return [utils.markInputRule(/(?:`)([^`]+)(?:`)$/, type)];
	}

	keys() {
		return {
			"Mod-`": () => this.toggle()
		};
	}

	get name() {
		return "code";
	}

	pasteRules({ type, utils }: MarkContext): Plugin[] {
		return [utils.markPasteRule(/(?:`)([^`]+)(?:`)/g, type)];
	}

	get schema(): MarkSpec {
		return {
			excludes: "_",
			parseDOM: [{ tag: "code" }],
			toDOM: () => ["code", 0]
		};
	}
}
