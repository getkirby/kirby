import Mark from "../Mark";

export default class Italic extends Mark {
  get button() {
    return {
      icon: "italic",
      label: window.panel.$t("toolbar.button.italic")
    };
  }

  commands() {
    return () => this.toggle();
  }

  inputRules({ type, utils }) {
    return [
      utils.markInputRule(/(?:^|\s)((?:\*)((?:[^*]+))(?:\*))$/, type),
      utils.markInputRule(/(?:^|\s)((?:_)((?:[^_]+))(?:_))$/, type)
    ];
  }

  keys() {
    return {
      "Mod-i": () => this.toggle()
    };
  }

  get name() {
    return "italic";
  }

  pasteRules({ type, utils }) {
    return [
      utils.markPasteRule(/_([^_]+)_/g, type),
      utils.markPasteRule(/\*([^*]+)\*/g, type)
    ];
  }

  get schema() {
    return {
      parseDOM: [{ tag: "i" }, { tag: "em" }, { style: "font-style=italic" }],
      toDOM: () => ["em", 0]
    };
  }
}
