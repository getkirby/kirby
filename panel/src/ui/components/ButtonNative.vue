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
import button from "@/ui/mixins/button.js";
import tab from "@/ui/mixins/tab.js";

export default {
  mixins: [button, tab],
  props: {
    loading: Boolean,
    type: {
      type: String,
      default: "button"
    }
  }
};
</script>
