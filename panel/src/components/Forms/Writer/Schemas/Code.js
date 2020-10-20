import { Schema } from "prosemirror-model";

export default function () {
  return new Schema({
    nodes: {
      text: {},
      doc: {
        content: "code"
      },
      code: {
        content: "text*",
        marks: "",
        code: true,
        defining: true,
        parseDOM: [
          {
            tag: "pre",
            preserveWhitespace: "full"
          }
        ],
        toDOM() {
          return ["pre", ["code", 0]];
        }
      },
    }
  });
};
