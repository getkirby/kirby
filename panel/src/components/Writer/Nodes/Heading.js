import Node from "../Node";
import Vue from "vue";

export default class Heading extends Node {

  get button() {
    return this.options.levels.map(level => {
      return {
        command: `h${level}`,
        icon: "title",
        /**
         * @todo replace with `window.panel.$t()` after merging fiber
         */
        label: Vue.$t("toolbar.button.heading." + level),
        name: this.name
      }
    });
  }

  commands({ type, schema, utils }) {
    let commands = {
      "toggleHeading": attrs => utils.toggleBlockType(type, schema.nodes.paragraph, attrs),
    };

    this.options.levels.forEach(level => {
      commands[`h${level}`] = () => utils.toggleBlockType(type, schema.nodes.paragraph, { level });
    });

    return commands;
  }

  get defaults() {
    return {
      levels: [1, 2, 3],
    };
  }

  inputRules({ type, utils }) {
    return this.options.levels.map(level => utils.textblockTypeInputRule(
      new RegExp(`^(#{1,${level}})\\s$`),
      type,
      () => ({ level }),
    ));
  }

  keys({ type, utils }) {
    return this.options.levels.reduce((items, level) => ({
      ...items,
      ...{
        [`Shift-Ctrl-${level}`]: utils.setBlockType(type, { level }),
      },
    }), {});
  }

  get name() {
    return "heading";
  }

  get schema() {
    return {
      attrs: {
        level: {
          default: 1,
        },
      },
      content: "inline*",
      group: "block",
      defining: true,
      draggable: false,
      parseDOM: this.options.levels
        .map(level => ({
          tag: `h${level}`,
          attrs: { level },
        })),
      toDOM: node => [`h${node.attrs.level}`, 0],
    };
  }

}
