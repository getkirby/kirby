import Node from "../Node";

export default class HardBreak extends Node {

  get name() {
    return "hardBreak";
  }

  get schema() {
    return {
      inline: true,
      group: "inline",
      selectable: false,
      parseDOM: [
        { tag: "br" },
      ],
      toDOM: () => ["br"],
    };
  }

  createHardBreak(utils, type) {
    return utils.chainCommands(utils.exitCode, (state, dispatch) => {
      dispatch(state.tr.replaceSelectionWith(type.create()).scrollIntoView());
      return true;
    });
  }

  commands({ utils, type }) {
    return () => this.createHardBreak(utils, type);
  }

  keys({ utils, type }) {
    const command = this.createHardBreak(utils, type);
    return {
      "Mod-Enter": command,
      "Shift-Enter": command,
    }
  }

}
