import Extension from "../Extension";
import { DOMParser } from "prosemirror-model";

export default class Insert extends Extension {
  commands() {
    return {
      insertHtml: (value) => (state, dispatch) => {
        let dom = document.createElement("div");
        dom.innerHTML = value.trim();
        const node = DOMParser.fromSchema(state.schema).parse(dom);
        dispatch(state.tr.replaceSelectionWith(node).scrollIntoView());
      }
    };
  }
}
