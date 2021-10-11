import Node from "../Node";

export default class HorizontalRule extends Node {
  commands({ type }) {
    return () => (state, dispatch) =>
      dispatch(state.tr.replaceSelectionWith(type.create()));
  }

  inputRules({ type, utils }) {
    return [utils.nodeInputRule(/^(?:---|___\s|\*\*\*\s)$/, type)];
  }

  get name() {
    return "horizontalRule";
  }

  get schema() {
    return {
      group: "block",
      parseDOM: [{ tag: "hr" }],
      toDOM: () => ["hr"]
    };
  }
}
