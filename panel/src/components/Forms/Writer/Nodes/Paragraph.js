import Node from "../Node";

export default class Paragraph extends Node {
  get button() {
    return {
      id: this.name,
      icon: "paragraph",
      label: window.panel.$t("toolbar.button.paragraph"),
      name: this.name
    };
  }

  commands({ utils, type }) {
    return {
      paragraph: () => utils.setBlockType(type)
    };
  }

  get schema() {
    return {
      content: "inline*",
      group: "block",
      draggable: false,
      parseDOM: [
        {
          tag: "p"
        }
      ],
      toDOM: () => ["p", 0]
    };
  }

  get name() {
    return "paragraph";
  }
}
