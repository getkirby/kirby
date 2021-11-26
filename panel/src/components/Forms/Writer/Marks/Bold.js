import Mark from "../Mark";

export default class Bold extends Mark {
  get button() {
    return {
      icon: "bold",
      label: window.panel.$t("toolbar.button.bold")
    };
  }

  commands() {
    return () => this.toggle();
  }

  inputRules({ type, utils }) {
    return [utils.markInputRule(/(?:\*\*|__)([^*_]+)(?:\*\*|__)$/, type)];
  }

  keys() {
    return {
      "Mod-b": () => this.toggle()
    };
  }

  get name() {
    return "bold";
  }

  pasteRules({ type, utils }) {
    return [utils.markPasteRule(/(?:\*\*|__)([^*_]+)(?:\*\*|__)/g, type)];
  }

  get schema() {
    return {
      parseDOM: [
        {
          tag: "strong"
        },
        {
          tag: "b",
          getAttrs: (node) => node.style.fontWeight !== "normal" && null
        },
        {
          style: "font-weight",
          getAttrs: (value) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null
        }
      ],
      toDOM: () => ["strong", 0]
    };
  }
}
