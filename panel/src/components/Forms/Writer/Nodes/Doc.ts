import type { NodeSpec } from "prosemirror-model";
import Node from "../Node";

export default class Doc extends Node<{ inline: boolean }> {
	get defaults() {
		return {
			inline: false
		};
	}

	get name() {
		return "doc";
	}

	get schema(): NodeSpec {
		return {
			content: this.options.inline ? "inline*" : "block+"
		};
	}
}
