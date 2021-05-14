import Mark from "../Mark";
import Vue from "vue";

export default class Code extends Mark {

  get button() {
    return {
      icon: "code",
      /**
       * @todo replace with `window.panel.$t()` after merging Inertia
       */
      label: Vue.$t("toolbar.button.code")
    };
  }

  commands() {
    return () => this.toggle();
  }

  inputRules({ type, utils }) {
    return [
      utils.markInputRule(/(?:`)([^`]+)(?:`)$/, type),
    ];
  }

  keys() {
    return {
      "Mod-`": () => this.toggle(),
    };
  }

  get name() {
    return "code";
  }

  pasteRules({ type, utils }) {
    return [
      utils.markPasteRule(/(?:`)([^`]+)(?:`)/g, type),
    ]
  }

  get schema() {
    return {
      excludes: "_",
      parseDOM: [
        { tag: "code" },
      ],
      toDOM: () => ["code", 0],
    };
  }

}
