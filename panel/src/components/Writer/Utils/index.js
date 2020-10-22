import {
  chainCommands,
  exitCode,
  toggleMark,
} from "prosemirror-commands";

import getMarkAttrs from './getMarkAttrs';
import markInputRule from "./markInputRule";
import markIsActive from './markIsActive';
import markPasteRule from "./markPasteRule";
import minMax from './minMax';
import nodeIsActive from './nodeIsActive';
import removeMark from "./removeMark";
import pasteRule from "./pasteRule";
import updateMark from "./updateMark";

export default {
  // Prosemirror
  chainCommands,
  exitCode,

  // Custom
  getMarkAttrs,
  markInputRule,
  markIsActive,
  markPasteRule,
  minMax,
  nodeIsActive,
  pasteRule,
  removeMark,
  toggleMark,
  updateMark
};
