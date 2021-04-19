import Node from "../Node";

export default class BulletList extends Node {

  get button() {
    return {
      label: "Bullet list",
      icon: "list-bullet",
    };
  }

  commands({ type, schema, utils }) {
    return () => utils.toggleList(type, schema.nodes.listItem);
  }

  inputRules({ type, utils }) {
    return [
      utils.wrappingInputRule(/^\s*([-+*])\s$/, type),
    ];
  }

  keys({ type, schema, utils }) {
    return {
      "Shift-Ctrl-8": utils.toggleList(type, schema.nodes.listItem),
    };
  }

  get name() {
    return "bulletList";
  }

  get schema() {
    return {
      content: "listItem+",
      group: "block",
      parseDOM: [
        { tag: "ul" },
      ],
      toDOM: () => ["ul", 0],
    };
  }

}
