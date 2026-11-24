import Node from "../Node";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default class Text extends Node {
	get name() {
		return "text";
	}

	get schema() {
		return {
			group: "inline"
		};
	}
}
