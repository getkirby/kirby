import { EditorView } from "prosemirror-view";

import Document from "./Document.js";
import Keymap from "./Keymap.js";
import Schema from "./Schema.js";
import State from "./State.js";

export default function (props) {
  const schema   = Schema(props.marks, props.code);
  const document = Document(schema, props.content, props.code);
  const keymap   = Keymap(props)
  const state    = State(document, keymap, props);

  return new EditorView(props.element, {
    state: state,
    editable() {
      return props.disabled === true ? false : true;
    },
    dispatchTransaction(transaction) {

      if (props.disabled === true) {
        return false;
      }

      const lastState = this.state;
      const nextState = this.state.apply(transaction);

      this.updateState(nextState);

      if (props.onUpdate && (!lastState || !lastState.doc.eq(this.state.doc))) {
        props.onUpdate();
      }

      if (!props.onSelect) {
        return;
      }

      // Don't do anything if the document/selection didn't change
      if (lastState && lastState.doc.eq(this.state.doc) && lastState.selection.eq(this.state.selection)) {
        return;
      }

      props.onSelect();

    },
    handlePaste(view, event, slice) {

      if (props.code) {
        return false;
      }

      let html = event.clipboardData.getData('text/html');
      let text = event.clipboardData.getData('text/plain');

      if (props.onPaste) {

        // plain text
        if (html.length === 0) {
          html = "<p>" + text + "</p>";
          html = html.replace(/[\n\r]{2}/g, "</p><p>");
          html = html.replace(/[\n\r]{1}/g, "<br>");

          props.onPaste(html, text);
        } else {
          props.onPaste(html, text);
        }

        return true;

      } else {
        return false;
      }

    }
  });
};


