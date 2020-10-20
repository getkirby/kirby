import { keymap } from "prosemirror-keymap";
import { history } from "prosemirror-history";
import { EditorState } from "prosemirror-state";
import {
  InputRule,
  inputRules,
  ellipsis
} from "prosemirror-inputrules"

const textRule = function (regex, handler) {
  return new InputRule(regex, (state, match, start, end) => {
    handler();
    return state.tr.delete(start, end);
  });
};

export default function (document, keys, props) {

  return EditorState.create({
    doc: document,
    plugins: [
      keymap(keys),
      history(),
      inputRules({
        rules: [
          ellipsis,
          new InputRule(/\<\-/, "←"),
          new InputRule(/\-\>/, "→"),
          new InputRule(/\-\-/, "–"),
          textRule(/^\–\-/, () => {
            if (props.onConvert) {
              props.onConvert("hr");
            }
          }),
          textRule(/^\s*([-+*])\s$/, () => {
            if (props.onConvert) {
              props.onConvert("ul");
            }
          }),
          textRule(/^\d{1,}\.\s$/, () => {
            if (props.onConvert) {
              props.onConvert("ol");
            }
          }),
          textRule(/^```$/, () => {
            if (props.onConvert) {
              props.onConvert("code");
            }
          }),
          textRule(/^\>\s$/, () => {
            if (props.onConvert) {
              props.onConvert("blockquote");
            }
          }),
          textRule(/^\#\s$/, () => {
            if (props.onConvert) {
              props.onConvert("h1");
            }
          }),
          textRule(/^\#\#\s$/, () => {
            if (props.onConvert) {
              props.onConvert("h2");
            }
          }),
          textRule(/^\#\#\#\s$/, () => {
            if (props.onConvert) {
              props.onConvert("h3");
            }
          }),
        ]
      }),
    ]
  });
};
