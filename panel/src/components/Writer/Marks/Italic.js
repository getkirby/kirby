import Mark from "../Mark";
import Vue from "vue";

export default class Italic extends Mark {

  get button() {
    return {
      icon: "italic",
      /**
       * @todo replace with `window.panel.$t()` after merging Inertia
       */
      label: Vue.$t("toolbar.button.italic")
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
