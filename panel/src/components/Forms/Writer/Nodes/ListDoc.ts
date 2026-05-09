import type { NodeSpec } from "prosemirror-model";
import Node from "../Node";

export default class ListDoc extends Node<{ nodes: string[] }> {
	get name() {
		return "doc";
	}

	get schema(): NodeSpec {
		return {
			content: this.options.nodes.join("|")
		};
	}
}
