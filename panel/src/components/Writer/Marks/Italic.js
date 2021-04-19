import Mark from "../Mark";

export default class Italic extends Mark {

  get button() {
    return {
      icon: "italic",
      label: "Italic"
    };
  }

  commands() {
    return () => this.toggle();
  }

  inputRules({ type, utils }) {
    return [
      utils.markInputRule(/(?:^|[^_])(_([^_]+)_)$/, type),
      utils.markInputRule(/(?:^|[^*])(\*([^*]+)\*)$/, type),
    ];
  }

  keys() {
    return {
      "Mod-i": () => this.toggle(),
    };
  }

  get name() {
    return "italic";
  }

  pasteRules({ type, utils }) {
    return [
      utils.markPasteRule(/_([^_]+)_/g, type),
      utils.markPasteRule(/\*([^*]+)\*/g, type),
    ];
  }

  get schema() {
    return {
      parseDOM: [
        { tag: "i" },
        { tag: "em" },
        { style: "font-style=italic" },
      ],
      toDOM: () => ['em', 0],
    };
  }

}
