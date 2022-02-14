<template>
  <k-link
    :id="id"
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
    <k-icon v-if="icon" :type="icon" :alt="tooltip" class="k-button-icon" />
    <span v-if="$slots.default" class="k-button-text"><slot /></span>
  </k-link>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    current: [String, Boolean],
    icon: String,
    id: [String, Number],
    link: String,
    rel: String,
    responsive: Boolean,
    role: String,
    target: String,
    tabindex: String,
    theme: String,
    tooltip: String
  },
  methods: {
    focus() {
      this.$el.focus();
    }
  }
};
</script>
