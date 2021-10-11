import Node from "../Node";

export default class ListItem extends Node {
  keys({ type, utils }) {
    return {
      Enter: utils.splitListItem(type),
      "Shift-Tab": utils.liftListItem(type),
      Tab: utils.sinkListItem(type)
    };
  }

  get name() {
    return "listItem";
  }

  get schema() {
    return {
      content: "paragraph block*",
      defining: true,
      draggable: false,
      parseDOM: [{ tag: "li" }],
      toDOM: () => ["li", 0]
    };
  }
}
