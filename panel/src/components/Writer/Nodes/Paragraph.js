import Node from "../Node";

export default class Paragraph extends Node {

  get name() {
    return "paragraph"
  }

  get schema() {
    return {
      content: "inline*",
      group: "block",
      draggable: false,
      parseDOM: [{
        tag: "p",
      }],
      toDOM: () => ["p", 0],
    }
  }

  commands({ editor, type }) {
    return {
      "paragraph": () => editor.command("setBlockType", type)
    };
  }

}
