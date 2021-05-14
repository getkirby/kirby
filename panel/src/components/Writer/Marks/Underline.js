import Mark from "../Mark";
import Vue from "vue";

export default class Underline extends Mark {

  get button() {
    return {
      icon: "underline",
      /**
       * @todo replace with `window.panel.$t()` after merging Inertia
       */
      label: Vue.$t("toolbar.button.underline")
    };
  }

  commands() {
    return () => this.toggle();
  }

  keys() {
    return {
      "Mod-u": () => this.toggle(),
    }
  }

  get name() {
    return "underline";
  }

  get schema() {
    return {
      parseDOM: [
        {
          tag: "u",
        },
        {
          style: "text-decoration",
          getAttrs: value => value === "underline",
        },
      ],
      toDOM: () => ["u", 0],
    }
  }

}
