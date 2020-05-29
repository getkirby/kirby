<template>
  <button
    :id="id"
    :aria-current="current"
    :accesskey="accesskey"
    :autofocus="autofocus"
    :data-theme="theme"
    :data-responsive="responsive"
    :role="role"
    :tabindex="tabindex"
    :title="tooltip || text"
    :type="type"
    class="k-button"
    v-on="$listeners"
  >
    <template v-if="icon">
      <k-loader
        v-if="loading"
        class="k-button-icon"
      />
      <k-icon
        v-else
        v-bind="iconOptions"
        class="k-button-icon"
      />
    </template>
    <span
      v-if="$slots.default"
      class="k-button-text"
    ><slot /></span>
  </button>
</template>

<script>
import tab from "../mixins/tab.js";

export default {
  mixins: [tab],
  inheritAttrs: false,
  props: {
    accesskey: String,
    autofocus: Boolean,
    current: [String, Boolean],
    icon: [String, Object],
    id: [String, Number],
    loading: Boolean,
    responsive: Boolean,
    role: String,
    tabindex: String,
    text: String,
    theme: String,
    tooltip: String,
    type: {
      type: String,
      default: "button"
    }
  },
  computed: {
    iconOptions() {
      if (typeof this.icon === "object") {
        return {
          ...this.icon,
          alt: this.tooltip
        };
      }

      return {
        type: this.icon,
        alt: this.tooltip
      };
    }
  }
};
</script>
