import { Plugin } from "prosemirror-state";
import { keymap } from "prosemirror-keymap";
import utils from "./Utils";

export default class Extensions {
  constructor(extensions = [], editor) {
    extensions.forEach((extension) => {
      extension.bindEditor(editor);
      extension.init();
    });
    this.extensions = extensions;
  }

  commands({ schema, view }) {
    return this.extensions
      .filter((extension) => extension.commands)
      .reduce((allCommands, extension) => {
        const { name, type } = extension;
        const commands = {};

        /**
         * get the commands for the current extension
         * by calling the commands creator function and
         * passing the schema, utils and the type
         */
        const value = extension.commands({
          schema,
          utils,
          ...(["node", "mark"].includes(type)
            ? {
                type: schema[`${type}s`][name]
              }
            : {})
        });

        /**
         * commands are wrapped in a little bit of common
         * logic to stop commands for disabled editors
         * or focus the view before the command is executed
         */
        const createCommand = (commandName, commandCallback) => {
          commands[commandName] = (attrs) => {
            if (typeof commandCallback !== "function" || !view.editable) {
              return false;
            }

            view.focus();

            const result = commandCallback(attrs);

            if (typeof result === "function") {
              return result(view.state, view.dispatch, view);
            }

            return result;
          };
        };

        /**
         * extensions can return an object with multiple
         * commands. The object key is the command name.
         */
        if (typeof value === "object") {
          Object.entries(value).forEach(([commandName, commandCallback]) => {
            createCommand(commandName, commandCallback);
          });

          /**
           * the extension name will be used as command name
           */
        } else {
          createCommand(name, value);
        }

        return {
          ...allCommands,
          ...commands
        };
      }, {});
  }

  buttons(type = "mark") {
    const buttons = {};

    this.extensions
      .filter((extension) => extension.type === type)
      .filter((extension) => extension.button)
      .forEach((extension) => {
        if (Array.isArray(extension.button)) {
          extension.button.forEach((button) => {
            buttons[button.id || button.name] = button;
          });
        } else {
          buttons[extension.name] = extension.button;
        }
      });

    return buttons;
  }

  getAllowedExtensions(excludedExtensions) {
    if (!(excludedExtensions instanceof Array) && excludedExtensions) return [];

    return excludedExtensions instanceof Array
      ? this.extensions.filter(
          (extension) => !excludedExtensions.includes(extension.name)
        )
      : this.extensions;
  }

  getFromExtensions(feature, params, extensions = this.extensions) {
    return extensions
      .filter((extension) => ["extension"].includes(extension.type))
      .filter((extension) => extension[feature])
      .map((extension) =>
        extension[feature]({
          ...params,
          utils
        })
      );
  }

  getFromNodesAndMarks(feature, params, extensions = this.extensions) {
    return extensions
      .filter((extension) => ["node", "mark"].includes(extension.type))
      .filter((extension) => extension[feature])
      .map((extension) =>
        extension[feature]({
          ...params,
          type: params.schema[`${extension.type}s`][extension.name],
          utils
        })
      );
  }

  inputRules({ schema, excludedExtensions }) {
    const allowedExtensions = this.getAllowedExtensions(excludedExtensions);
    const fromExtensions = this.getFromExtensions(
      "inputRules",
      { schema },
      allowedExtensions
    );
    const fromNodesAndMarks = this.getFromNodesAndMarks(
      "inputRules",
      { schema },
      allowedExtensions
    );

    return [...fromExtensions, ...fromNodesAndMarks].reduce(
      (allInputRules, inputRules) => [...allInputRules, ...inputRules],
      []
    );
  }

  keymaps({ schema }) {
    const fromExtensions = this.getFromExtensions("keys", { schema });
    const fromNodesAndMarks = this.getFromNodesAndMarks("keys", { schema });

    return [...fromExtensions, ...fromNodesAndMarks].map((keys) =>
      keymap(keys)
    );
  }

  get marks() {
    return this.extensions
      .filter((extension) => extension.type === "mark")
      .reduce(
        (marks, { name, schema }) => ({
          ...marks,
          [name]: schema
        }),
        {}
      );
  }

  get nodes() {
    return this.extensions
      .filter((extension) => extension.type === "node")
      .reduce(
        (nodes, { name, schema }) => ({
          ...nodes,
          [name]: schema
        }),
        {}
      );
  }

  get options() {
    const { view } = this;
    return this.extensions.reduce(
      (nodes, extension) => ({
        ...nodes,
        [extension.name]: new Proxy(extension.options, {
          set(obj, prop, value) {
            const changed = obj[prop] !== value;

            Object.assign(obj, { [prop]: value });

            if (changed) {
              view.updateState(view.state);
            }

            return true;
          }
        })
      }),
      {}
    );
  }

  pasteRules({ schema, excludedExtensions }) {
    const allowedExtensions = this.getAllowedExtensions(excludedExtensions);
    const fromExtensions = this.getFromExtensions(
      "pasteRules",
      { schema },
      allowedExtensions
    );
    const fromNodesAndMarks = this.getFromNodesAndMarks(
      "pasteRules",
      { schema },
      allowedExtensions
    );

    return [...fromExtensions, ...fromNodesAndMarks].reduce(
      (allPasteRules, pasteRules) => [...allPasteRules, ...pasteRules],
      []
    );
  }

  plugins({ schema }) {
    const fromExtensions = this.getFromExtensions("plugins", { schema });
    const fromNodesAndMarks = this.getFromNodesAndMarks("plugins", { schema });

    return [...fromExtensions, ...fromNodesAndMarks]
      .reduce((allPlugins, plugins) => [...allPlugins, ...plugins], [])
      .map((plugin) => {
        if (plugin instanceof Plugin) {
          return plugin;
        }

        return new Plugin(plugin);
      });
  }
}
