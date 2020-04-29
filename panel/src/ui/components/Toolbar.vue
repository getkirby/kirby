<template>
  <nav
    v-if="buttonSetup.length"
    class="k-toolbar bg-white"
  >
    <div class="k-toolbar-wrapper">
      <div class="k-toolbar-buttons flex">
        <template v-for="(button, buttonIndex) in buttonSetup">
          <!-- divider -->
          <template v-if="button === '|'">
            <span
              :key="buttonIndex"
              class="k-toolbar-divider"
            />
          </template>

          <!-- dropdown -->
          <template v-else-if="button.dropdown">
            <k-dropdown :key="buttonIndex">
              <k-button
                :key="buttonIndex"
                :icon="button.icon"
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
                  @click="onCommand(dropdownItem.command, dropdownItem.args)"
                >
                  {{ dropdownItem.label }}
                </k-dropdown-item>
              </k-dropdown-content>
            </k-dropdown>
          </template>

          <!-- single button -->
          <template v-else>
            <k-button
              :key="buttonIndex"
              :icon="button.icon"
              :tooltip="button.label"
              tabindex="-1"
              class="k-toolbar-button"
              @click="onCommand(button.command, button.args)"
            />
          </template>
        </template>
      </div>
    </div>
  </nav>
</template>

<script>
import systemButtons from "./ToolbarButtons.js";

export default {
  props: {
    buttons: {
      type: Object
    },
    layout: {
      type: [Boolean, Array],
      default: true,
    },
    options: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {
    buttonDefinitions() {
      let config = {};

      const buttons = {
        ...systemButtons,
        ...this.buttons
      };

      Object.keys(buttons).forEach(buttonName => {
        const defaults = {
          command: buttonName,
          icon: buttonName,
          label: this.$t("toolbar.button." + buttonName),
          name: buttonName
        };

        const definition = buttons[buttonName](this, this.options[buttonName] || {});

        if (definition === false) {
          return true;
        }

        config[buttonName] = {
          ...defaults,
          ...definition
        };

        // dropdown clean-up
        if (config[buttonName].dropdown) {
          // convert dropdown to array
          config[buttonName].dropdown = Object.values(config[buttonName].dropdown);

          // convert single option dropdown to regular button
          if (config[buttonName].dropdown.length === 1) {
            const firstOption = config[buttonName].dropdown[0];

            config[buttonName].args    = firstOption.args;
            config[buttonName].command = firstOption.command;
            config[buttonName].label   = firstOption.label;

            delete config[buttonName].dropdown;
          }
        }

      });

      return config;
    },
    buttonSetup() {

      let layout = this.layout;

      // disabled buttons
      if (layout === false) {
        return [];
      }

      // default layout
      if (layout === true) {
        layout = [
          "headings",
          "bold",
          "italic",
          "|",
          "link",
          "email",
          "file",
          "|",
          "code",
          "ul",
          "ol"
        ];
      }

      let setup = [];

      layout.forEach(buttonName => {
        if (buttonName === "|") {
          setup.push("|");
        }

        if (this.buttonDefinitions[buttonName]) {
          setup.push(this.buttonDefinitions[buttonName]);
        }
      });

      return setup;
    }
  },
  methods: {
    onCommand(command, args) {
      if (!args) {
        this.$emit("command", command);
        return;
      }

      if (Array.isArray(args) === true) {
        this.$emit("command", command, ...args);
        return;
      }

      this.$emit("command", command, args);
    },
    shortcut(key) {
      this.buttonSetup.forEach(button => {
        if (button.dropdown) {
          Object.values(button.dropdown).forEach(dropdown => {
            if (dropdown.shortcut && dropdown.shortcut === key) {
              this.onCommand(dropdown.command, dropdown.args);
            }
          });
        } else if (button.shortcut && button.shortcut === key) {
          this.onCommand(button.command, button.args);
        }
      });
    }
  }
};
</script>

<style lang="scss">
.k-toolbar {
  height: 36px;
}
.k-toolbar-wrapper {
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  max-width: 100%;
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
