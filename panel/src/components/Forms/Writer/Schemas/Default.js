import { Schema } from "prosemirror-model";

/* Marks */
import CodeMark from "../Marks/Code.js";
import BoldMark from "../Marks/Bold.js";
import ItalicMark from "../Marks/Italic.js";
import LinkMark from "../Marks/Link.js";
import StrikeThroughMark from "../Marks/StrikeThrough.js";
import UnderlineMark from "../Marks/Underline.js";

export default function (marks) {

  const availableMarks = {
    code: CodeMark,
    bold: BoldMark,
    italic: ItalicMark,
    link: LinkMark,
    underline: UnderlineMark,
    strikeThrough: StrikeThroughMark
  };

  let allowedMarks = {};

  marks.forEach(mark => {
    allowedMarks[mark] = availableMarks[mark];
  });

  return new Schema({
    nodes: {
      text: {},
      doc: {
        content: "(text | hard_break )*",
      },
      hard_break: {
        inline: true,
        group: "inline",
        selectable: false,
        parseDOM: [
          { tag: "br" }
        ],
        toDOM() {
          return ["br"];
        }
      }
    },
    marks: allowedMarks
  });
};
