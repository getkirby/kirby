// prosemirror
import {
  chainCommands,
  exitCode,
  setBlockType,
  toggleMark
} from "prosemirror-commands";

// prosemirror-inputrules
import {
  wrappingInputRule,
  textblockTypeInputRule
} from "prosemirror-inputrules";

import {
  addListNodes,
  wrapInList,
  splitListItem,
  liftListItem,
  sinkListItem
} from "prosemirror-schema-list";

// custom
import getMarkAttrs from "./getMarkAttrs";
import markInputRule from "./markInputRule";
import markIsActive from "./markIsActive";
import markPasteRule from "./markPasteRule";
import minMax from "./minMax";
import nodeInputRule from "./nodeInputRule";
import nodeIsActive from "./nodeIsActive";
import removeMark from "./removeMark";
import pasteRule from "./pasteRule";
import toggleBlockType from "./toggleBlockType";
import toggleList from "./toggleList";
import updateMark from "./updateMark";
import getNodeAttrs from "./getNodeAttrs";

export default {
  // prosemirror
  chainCommands,
  exitCode,
  setBlockType,
  toggleMark,

  // prosemirror-inputrules
  wrappingInputRule,
  textblockTypeInputRule,

  // prosemirror-schema-list
  addListNodes,
  wrapInList,
  splitListItem,
  liftListItem,
  sinkListItem,

  // custom
  getMarkAttrs,
  getNodeAttrs,
  markInputRule,
  markIsActive,
  markPasteRule,
  minMax,
  nodeIsActive,
  nodeInputRule,
  pasteRule,
  removeMark,
  toggleBlockType,
  toggleList,
  updateMark
};
