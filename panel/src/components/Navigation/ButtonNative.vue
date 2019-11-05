<template>
  <button
    :aria-current="current"
    :autofocus="autofocus"
    :id="id"
    :data-theme="theme"
    :data-responsive="responsive"
    :role="role"
    :tabindex="tabindex"
    :title="tooltip"
    :type="type"
    class="k-button"
    v-on="$listeners"
  >
    <k-icon
      v-if="icon"
      :type="icon"
      :alt="tooltip"
      class="k-button-icon"
    />
    <span v-if="$slots.default" class="k-button-text"><slot /></span>
  </button>
</template>

<script>
import tab from "@/mixins/tab.js";

export default {
  mixins: [tab],
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    current: [String, Boolean],
    icon: String,
    id: [String, Number],
    responsive: Boolean,
    role: String,
    tabindex: String,
    theme: String,
    tooltip: String,
    type: {
      type: String,
      default: "button"
    }
  }
};
</script>
