import Node from "../Node";

export default class HorizontalRule extends Node {
	commands({ type, utils }) {
		return () => utils.insertNode(type);
	}

	inputRules({ type, utils }) {
		// create regular input rule for horizontal rule
		const rule = utils.nodeInputRule(/^(?:---|___\s|\*\*\*\s)$/, type);

		// extend handler to remove the leftover empty line
		const handler = rule.handler;
		rule.handler = (state, match, start, end) =>
			handler(state, match, start, end).replaceWith(start - 1, start, "");

		return [rule];
	}

	get name() {
		return "horizontalRule";
	}

	get schema() {
		return {
			group: "block",
			parseDOM: [{ tag: "hr" }],
			toDOM: () => ["hr"]
		};
	}
}
