<template>
  <k-link
    :id="id"
    :accesskey="accesskey"
    :aria-current="current"
    :autofocus="autofocus"
    :data-theme="theme"
    :data-responsive="responsive"
    :rel="rel"
    :role="role"
    :tabindex="tabindex"
    :target="target"
    :title="tooltip"
    :to="link"
    class="k-button"
    v-on="$listeners"
  >
    <k-icon
      v-if="icon"
      v-bind="iconOptions"
      class="k-button-icon"
    />
    <span
      v-if="$slots.default"
      class="k-button-text"
    ><slot /></span>
  </k-link>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    accesskey: String,
    autofocus: Boolean,
    current: [String, Boolean],
    icon: [String, Object],
    id: [String, Number],
    link: String,
    rel: String,
    responsive: Boolean,
    role: String,
    target: String,
    tabindex: String,
    theme: String,
    tooltip: String,
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
