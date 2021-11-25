import Node from "../Node";

export default class HardBreak extends Node {
  commands({ utils, type }) {
    return () => this.createHardBreak(utils, type);
  }

  createHardBreak(utils, type) {
    return utils.chainCommands(utils.exitCode, (state, dispatch) => {
      dispatch(state.tr.replaceSelectionWith(type.create()).scrollIntoView());
      return true;
    });
  }

  get defaults() {
    return {
      enter: false,
      text: false
    };
  }

  keys({ utils, type }) {
    const command = this.createHardBreak(utils, type);

    let keymap = {
      "Mod-Enter": command,
      "Shift-Enter": command
    };

    if (this.options.enter) {
      keymap["Enter"] = command;
    }

    return keymap;
  }

  get name() {
    return "hardBreak";
  }

  get schema() {
    return {
      inline: true,
      group: "inline",
      selectable: false,
      parseDOM: [{ tag: "br" }],
      toDOM: () => ["br"]
    };
  }
}
