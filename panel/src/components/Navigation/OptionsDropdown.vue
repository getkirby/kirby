<template>
  <!-- Single option = button -->
  <k-button
    v-if="hasSingleOption"
    v-bind="options[0]"
    :icon="options[0].icon || icon"
    :tooltip="options[0].tooltip || options[0].text"
    class="k-options-dropdown-toggle"
    @click="onAction(options[0].option || options[0].click, options[0], 0)"
  >
    <template v-if="text === true">
      {{ options[0].text }}
    </template>
    <template v-else-if="text !== false">
      {{ text }}
    </template>
  </k-button>

  <!-- Multiple options = dropdown -->
  <k-dropdown v-else-if="options.length" class="k-options-dropdown">
    <k-button
      :icon="icon"
      :tooltip="$t('options')"
      class="k-options-dropdown-toggle"
      @click="$refs.options.toggle()"
    >
      <template v-if="text && text !== true">
        {{ text }}
      </template>
    </k-button>
    <k-dropdown-content
      ref="options"
      :align="align"
      :options="options"
      class="k-options-dropdown-content"
      @action="onAction"
    />
  </k-dropdown>
</template>

<script>
export default {
  props: {
    /**
     * Aligment of the dropdown items
     * @values left, right
     */
    align: {
      type: String,
      default: "right"
    },
    /**
     * Icon for the dropdown button
     */
    icon: {
      type: String,
      default: "dots"
    },
    options: {
      type: [Array, Function, String],
      default() {
        return [];
      }
    },
    /**
     * Whether or which text to show
     * for the dropdown button
     */
    text: {
      type: [Boolean, String],
      default: true
    },
    /**
     * Visual theme of the dropdown
     * @values dark, light
     */
    theme: {
      type: String,
      default: "dark"
    }
  },
  computed: {
    hasSingleOption() {
      return Array.isArray(this.options) && this.options.length === 1;
    }
  },
  methods: {
    onAction(action, item, itemIndex) {
      this.$emit("action", action, item, itemIndex);
      this.$emit("option", action, item, itemIndex);
    },
    toggle() {
      this.$refs.options.toggle();
    }
  }
};
</script>

<style>
.k-options-dropdown {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 38px;
}
.k-options-dropdown-toggle {
  display: flex;
  justify-content: center;
  align-items: center;
  min-width: 38px;
  height: 38px;
  padding: 0 0.75rem;
}
</style>
