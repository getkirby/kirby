import Extension from "./Extension";

export default class Node extends Extension {
	get type() {
		return "node";
	}

	get schema() {
		return {};
	}

	get view() {
		return undefined;
	}
}
