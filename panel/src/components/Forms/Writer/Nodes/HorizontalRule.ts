import type { InputRule } from "prosemirror-inputrules";
import type { NodeSpec } from "prosemirror-model";
import type { EditorState, Transaction } from "prosemirror-state";
import type { ExtensionCommand } from "../Extension";
import Node, { type NodeContext } from "../Node";

type InputRuleHandler = (
	state: EditorState,
	match: RegExpMatchArray,
	start: number,
	end: number
) => Transaction | null;

export default class HorizontalRule extends Node {
	commands({ type, utils }: NodeContext): ExtensionCommand {
		return () => utils.insertNode(type);
	}

	inputRules({ type, utils }: NodeContext): InputRule[] {
		// create regular input rule for horizontal rule
		const rule = utils.nodeInputRule(/^(?:---|___\s|\*\*\*\s)$/, type);

		const { handler } = rule as unknown as { handler: InputRuleHandler };

		// extend handler to remove the leftover empty line
		return [
			Object.assign(rule, {
				handler: (
					state: EditorState,
					match: RegExpMatchArray,
					start: number,
					end: number
				) =>
					handler(state, match, start, end)?.delete(start - 1, start)
			})
		];
	}

	get name() {
		return "horizontalRule";
	}

	get schema(): NodeSpec {
		return {
			group: "block",
			parseDOM: [{ tag: "hr" }],
			toDOM: () => ["hr"]
		};
	}
}
