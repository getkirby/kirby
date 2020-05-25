<template>
  <section
    :class="`k-${type}-section k-section-name-${sectionName}`"
    class="k-section"
  >
    <k-header-bar
      v-if="label || headline"
      :link="link"
      :options="options"
      :optionsText="true"
      :required="required"
      :text="label || headline"
      class="k-section-header"
      element="h2"
      @option="onOption"
    />
    <slot />
  </section>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    /**
     * Deprecated. Use label!
     */
    headline: String,
    label: {
      type: [Boolean, String],
    },
    link: {
      type: [Boolean, String],
      default: false,
    },
    options: {
      type: [Boolean, Array]
    },
    name: {
      type: String,
    },
    required: {
      type: Boolean,
      default: false,
    },
    type: {
      type: String,
    },
  },
  computed: {
    sectionName() {
      return this.name || this.type;
    }
  },
  methods: {
    onOption(option) {
      this.$emit("option", option);
    }
  }
};
</script>

<style lang="scss">
.k-section-header {
  z-index: 1;
  margin-top: -.75rem;
}
</style>
