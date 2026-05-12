import { DOMParser } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import Extension from "../Extension";

/**
 * Provides the `insertHtml` command, which parses an HTML string
 * and replaces the current selection with the resulting document nodes.
 */
export default class Insert extends Extension {
	commands() {
		return {
			insertHtml:
				(value: unknown): Command =>
				(state, dispatch) => {
					if (typeof value !== "string") {
						return false;
					}

					const dom = document.createElement("div");
					dom.innerHTML = value.trim();
					const { tr } = state;
					const node = DOMParser.fromSchema(state.schema).parse(dom);
					tr.replaceSelectionWith(node).scrollIntoView();

					if (dispatch) {
						dispatch(tr);
					}

					return true;
				}
		};
	}

	get name() {
		return "insert";
	}
}
