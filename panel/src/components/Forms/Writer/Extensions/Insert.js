import Extension from "../Extension";
import { DOMParser } from "prosemirror-model";

export default class Insert extends Extension {
	commands() {
		return {
			insertHtml: (value) => (state, dispatch) => {
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
