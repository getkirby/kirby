<template>
  <k-button
    :disabled="disabled"
    :icon="icon"
    :responsive="responsive"
    :text="text"
    :theme="theme"
    :tooltip="title"
    :class="'k-status-icon k-status-icon-' + status"
    @click="onClick"
  />
</template>

<script>
/**
 * Page status icon
 */
export default {
  props: {
    click: {
      type: Function,
      default: () => {}
    },
    disabled: Boolean,
    responsive: Boolean,
    status: String,
    text: String,
    tooltip: String
  },
  computed: {
    icon() {
      if (this.status === "draft") {
        return "circle-outline";
      }

      if (this.status === "unlisted") {
        return "circle-half";
      }

      return "circle";
    },
    theme() {
      if (this.status === "draft") {
        return "negative";
      }

      if (this.status === "unlisted") {
        return "info";
      }

      return "positive";
    },
    title() {
      let title = this.tooltip || this.text;

      if (this.disabled) {
        title += ` (${this.$t("disabled")})`;
      }

      return title;
    }
  },
  methods: {
    onClick() {
      this.click();
      this.$emit("click");
    }
  }
};
</script>

<style>
.k-status-icon svg {
  width: 14px;
  height: 14px;
}
.k-status-icon .k-icon {
  color: var(--theme-light);
}
.k-status-icon .k-button-text {
  color: var(--color-black);
}
.k-status-icon[data-disabled="true"] {
  opacity: 1 !important;
}
.k-status-icon[data-disabled="true"] .k-icon {
  color: var(--color-gray-400);
  opacity: 0.5;
}
</style>
