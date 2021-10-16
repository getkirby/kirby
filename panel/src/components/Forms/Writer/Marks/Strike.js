import Mark from "../Mark";

export default class Strike extends Mark {
  get button() {
    return {
      icon: "strikethrough",
      label: window.panel.$t("toolbar.button.strike")
    };
  }

  commands() {
    return () => this.toggle();
  }

  inputRules({ type, utils }) {
    return [utils.markInputRule(/~([^~]+)~$/, type)];
  }

  keys() {
    return {
      "Mod-d": () => this.toggle()
    };
  }

  get name() {
    return "strike";
  }

  pasteRules({ type, utils }) {
    return [utils.markPasteRule(/~([^~]+)~/g, type)];
  }

  get schema() {
    return {
      parseDOM: [
        {
          tag: "s"
        },
        {
          tag: "del"
        },
        {
          tag: "strike"
        },
        {
          style: "text-decoration",
          getAttrs: (value) => value === "line-through"
        }
      ],
      toDOM: () => ["s", 0]
    };
  }
}
