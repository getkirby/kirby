import Mark from "../Mark";

export default class Underline extends Mark {
  get button() {
    return {
      icon: "underline",
      label: window.panel.$t("toolbar.button.underline")
    };
  }

  commands() {
    return () => this.toggle();
  }

  keys() {
    return {
      "Mod-u": () => this.toggle()
    };
  }

  get name() {
    return "underline";
  }

  get schema() {
    return {
      parseDOM: [
        {
          tag: "u"
        },
        {
          style: "text-decoration",
          getAttrs: (value) => value === "underline"
        }
      ],
      toDOM: () => ["u", 0]
    };
  }
}
