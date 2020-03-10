<template>
  <span>
    <span class="k-status">
      <k-button
        v-bind="button"
        @click="onClick"
      >{{ page.label || "" }}</k-button>
      <k-icon
        v-if="isDisabled"
        type="lock"
        class="k-status-icon-lock"
      />
    </span>
  </span>
</template>

<script>
export default {
  props: {
    action: Function,
    page: Object
  },
  computed: {
    button() {
      return {
        class: "k-status-icon k-status-icon-" + this.page.status,
        icon: this.icon,
        tooltip: this.tooltip,
        disabled: this.isDisabled
      }
    },
    icon() {
      if (this.page.status === "draft") {
        return "protected";
      }

      if (this.page.status === "unlisted") {
        return "circle-outline";
      }

      return "circle";
    },
    isDisabled() {
      return this.page.permissions.changeStatus === false;
    },
    tooltip() {
      let tooltip = this.$t("page.status") + ": ";

      if (this.page.label) {
        tooltip += this.page.label;
      } else {
        tooltip += this.$t("page.status." + this.page.status);
      }

      if (this.isDisabled) {
        tooltip += " " + this.$t("disabled");
      }

      return tooltip;
    }
  },
  methods: {
    onClick() {
      if (this.action) {
        this.action();
        return;
      }

      this.$emit("click");
    }
  }
}
</script>

<style lang="scss">
.k-status {
  position: relative;
}

.k-status-icon svg {
  width: 14px;
  height: 14px;
}
.k-status-icon-listed .k-icon {
  color: $color-positive-on-dark;
}
.k-status-icon-unlisted .k-icon {
  color: $color-focus-on-dark;
}
.k-status-icon-draft .k-icon {
  color: $color-negative-on-dark;
}
.k-status-icon[disabled] {
  opacity: 1;
}

.k-status-icon-lock {
  position: absolute;
  left: 1px;
  bottom: -4px;
  transform: scale(0.6);
  transform-origin: right bottom;
  color: $color-dark-grey;
}
.k-card .k-status-icon-lock,
.k-list-item .k-status-icon-lock {
  bottom: -1px;
}

.k-status-icon .k-button-text:empty {
  display: none;
}
</style>
