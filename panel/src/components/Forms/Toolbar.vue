<template>
  <nav class="k-toolbar">
    <div class="k-toolbar-buttons">
      <template v-for="(button, buttonIndex) in layout">

        <!-- divider -->
        <template v-if="button.divider">
          <span :key="buttonIndex" class="k-toolbar-divider" />
        </template>

        <!-- dropdown -->
        <template v-else-if="button.dropdown">
          <k-dropdown :key="buttonIndex">
            <k-button
              :icon="button.icon"
              :key="buttonIndex"
              :tooltip="button.label"
              tabindex="-1"
              class="k-toolbar-button"
              @click="$refs[buttonIndex + '-dropdown'][0].toggle()"
            />
            <k-dropdown-content :ref="buttonIndex + '-dropdown'">
              <k-dropdown-item
                v-for="(dropdownItem, dropdownItemIndex) in button.dropdown"
                :key="dropdownItemIndex"
                :icon="dropdownItem.icon"
                @click="command(dropdownItem.command, dropdownItem.args)"
              >
                {{ dropdownItem.label }}
              </k-dropdown-item>
            </k-dropdown-content>
          </k-dropdown>
        </template>

        <!-- single button -->
        <template v-else>
          <k-button
            :icon="button.icon"
            :key="buttonIndex"
            :tooltip="button.label"
            tabindex="-1"
            class="k-toolbar-button"
            @click="command(button.command, button.args)"
          />
        </template>

      </template>
    </div>
  </nav>
</template>

<script>
const list = function(type) {
  this.command("insert", (input, selection) => {
    let html = [];

    selection.split("\n").forEach((line, index) => {
      let prepend = type === "ol" ? index + 1 + "." : "-";
      html.push(prepend + " " + line);
    });

    return html.join("\n");
  });
};

export default {
  layout: [
    "headlines",
    "bold",
    "italic",
    "|",
    "link",
    "email",
    "code",
    "|",
    "ul",
    "ol"
  ],
  props: {
    buttons: {
      type: [Boolean, Array],
      default: true
    }
  },
  data() {
    let layout = {};
    let shortcuts = {};
    let buttons = [];
    let commands = this.commands();

    if (this.buttons === false) {
      return layout;
    }

    if (Array.isArray(this.buttons)) {
      buttons = this.buttons;
    }

    if (Array.isArray(this.buttons) !== true) {
      buttons = this.$options.layout;
    }

    buttons.forEach((item, index) => {
      if (item === "|") {
        layout["divider-" + index] = { divider: true };
      } else if (commands[item]) {
        let button = commands[item];
        layout[item] = button;

        if (button.shortcut) {
          shortcuts[button.shortcut] = item;
        }
      }
    });

    return {
      layout: layout,
      shortcuts: shortcuts
    };
  },
  methods: {
    command(command, callback) {
      if (typeof command === "function") {
        command.apply(this);
      } else {
        this.$emit("command", command, callback);
      }
    },
    commands() {
      return {
        headlines: {
          label: this.$t("toolbar.button.headings"),
          icon: "title",
          dropdown: {
            h1: {
              label: this.$t("toolbar.button.heading.1"),
              icon: "title",
              command: "prepend",
              args: "#"
            },
            h2: {
              label: this.$t("toolbar.button.heading.2"),
              icon: "title",
              command: "prepend",
              args: "##"
            },
            h3: {
              label: this.$t("toolbar.button.heading.3"),
              icon: "title",
              command: "prepend",
              args: "###"
            }
          }
        },
        bold: {
          label: this.$t("toolbar.button.bold"),
          icon: "bold",
          command: "wrap",
          args: "**",
          shortcut: "b"
        },
        italic: {
          label: this.$t("toolbar.button.italic"),
          icon: "italic",
          command: "wrap",
          args: "*",
          shortcut: "i"
        },
        link: {
          label: this.$t("toolbar.button.link"),
          icon: "url",
          shortcut: "l",
          command: "dialog",
          args: "link"
        },
        email: {
          label: this.$t("toolbar.button.email"),
          icon: "email",
          shortcut: "e",
          command: "dialog",
          args: "email"
        },
        code: {
          label: this.$t("toolbar.button.code"),
          icon: "code",
          command: "wrap",
          args: "`"
        },
        ul: {
          label: this.$t("toolbar.button.ul"),
          icon: "list-bullet",
          command() {
            return list.apply(this, ["ul"]);
          }
        },
        ol: {
          label: this.$t("toolbar.button.ol"),
          icon: "list-numbers",
          command() {
            return list.apply(this, ["ol"]);
          }
        }
      };
    },
    shortcut(shortcut, $event) {
      if (this.shortcuts[shortcut]) {
        const button = this.layout[this.shortcuts[shortcut]];

        if (!button) {
          return false;
        }

        $event.preventDefault();

        this.command(button.command, button.args);
      }
    }
  }
};
</script>

<style lang="scss">
.k-toolbar {
  background: $color-white;
  border-bottom: 1px solid $color-background;
  height: 38px;
}
.k-toolbar-buttons {
  display: flex;
  flex-wrap: wrap;
}
.k-toolbar-divider {
  width: 1px;
  background: $color-background;
}
.k-toolbar-button {
  width: 36px;
  height: 36px;
}
.k-toolbar-button:hover {
  background: rgba($color-background, 0.5);
}
</style>
