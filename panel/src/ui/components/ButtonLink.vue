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
    :title="tooltip || text"
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
import button from "@/ui/mixins/button.js";

export default {
  mixins: [button],
  props: {
    link: String,
    rel: String,
    target: String,
  }
};
</script>
