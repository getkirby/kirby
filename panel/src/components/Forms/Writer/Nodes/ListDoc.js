import Doc from "./Doc";

export default class ListDoc extends Doc {
	get schema() {
		return {
			content: this.options.nodes.join("|")
		};
	}
}
