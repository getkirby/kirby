<template>
  <k-button
    :disabled="disabled"
    :icon="icon"
    :responsive="responsive"
    :tooltip="title"
    :class="'k-status-icon k-status-icon-' + status"
    @click="onClick"
  >
    <template v-if="text">
      {{ text }}
    </template>
  </k-button>
</template>

<script>
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

      return "circle"
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
}
</script>

<style>
.k-status-icon svg {
  width: 14px;
  height: 14px;
}
.k-status-icon-listed .k-icon {
  color: var(--color-positive-light);
}
.k-status-icon-unlisted .k-icon {
  color: var(--color-focus-light);
}
.k-status-icon-draft  .k-icon {
  color: var(--color-negative-light);
}
.k-status-icon[data-disabled] {
  opacity: 1 !important;
}
.k-status-icon[data-disabled] .k-icon {
  color: var(--color-gray-400);
  opacity: .5;
}
</style>
