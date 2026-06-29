import type { NodeSpec } from "prosemirror-model";
import Node from "../Node";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
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
