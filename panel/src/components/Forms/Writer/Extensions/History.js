import Extension from "../Extension";
import { history, undo, redo, undoDepth, redoDepth } from "prosemirror-history";

export default class History extends Extension {
  commands() {
    return {
      undo: () => undo,
      redo: () => redo,
      undoDepth: () => undoDepth,
      redoDepth: () => redoDepth
    };
  }

  get defaults() {
    return {
      depth: "",
      newGroupDelay: ""
    };
  }

  keys() {
    return {
      "Mod-z": undo,
      "Mod-y": redo,
      "Shift-Mod-z": redo,
      // Russian language
      "Mod-я": undo,
      "Shift-Mod-я": redo
    };
  }

  get name() {
    return "history";
  }

  plugins() {
    return [
      history({
        depth: this.options.depth,
        newGroupDelay: this.options.newGroupDelay
      })
    ];
  }
}
