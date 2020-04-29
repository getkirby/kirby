<template>
  <section
    :class="`k-${type}-section k-section-name-${sectionName}`"
    class="k-section"
  >
    <header
      v-if="label || headline"
      class="k-section-header"
    >
      <k-headline
        :link="link"
        :class="`k-${type}-section-headline`"
        class="k-section-headline"
      >
        {{ label || headline }}
        <abbr v-if="required" :title="$t('section.required')">*</abbr>
      </k-headline>

      <template v-if="options && options.length === 1">
        <k-button
          v-bind="options[0]"
          @click="onOption(options[0].option || options[0].click)"
        >
          {{ options[0].text }}
        </k-button>
      </template>
      <template v-else>
        <k-options-dropdown
          :options="options"
          @option="onOption"
        />
      </template>
    </header>
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
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1;
  height: 2.5rem;
  margin-top: -.75rem;
}
.k-section-header .k-headline .k-link {
  display: flex;
  align-items: center;
  padding: 0 .75rem;
  height: 2.5rem;
  margin-left: -.75rem;
}
</style>
