<template>
  <k-button
    :tooltip="tooltip || $t('tooltip.button.' + command)"
    :icon="icon || command"
    :class="'k-toolbar-button-' + command"
    class="k-toolbar-button"
    tabindex="-1"
    @click="onClick"
  />
</template>

<script>
export default {
  props: {
    args: {
      type: [String, Array],
      default() {
        return [];
      }
    },
    command: String,
    icon: String,
    shortcut: String,
    tooltip: String,
  },
  methods: {
    onClick(event) {
      let args = this.args;

      if (Array.isArray(args) === false) {
        args = [this.args];
      }

      this.$emit("click", event);
      this.$emit("command", this.command, ...args);
    }
  }
};
</script>
