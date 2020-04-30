<template>
  <k-button
    v-if="single && options[0]"
    :icon="options[0].icon"
    :theme="options[0].theme"
    :tooltip="options[0].text"
    class="k-options-dropdown-toggle"
    @click="onOption(options[0].option || options[0].click, options[0], 0)"
  >
    <template slot="default" v-if="text">{{ text }}</template>
  </k-button>
  <k-dropdown
    v-else-if="options.length"
    class="k-options-dropdown flex items-center justify-center"
  >
    <k-button
      :icon="icon || 'dots'"
      :tooltip="$t('options')"
      class="k-options-dropdown-toggle"
      @click="$refs.options.toggle()"
    >
      <template slot="default" v-if="text">{{ text }}</template>
    </k-button>
    <k-dropdown-content
      ref="options"
      :align="align"
      :options="options"
      :theme="theme"
      class="k-options-dropdown-content"
      @option="onOption"
    />
  </k-dropdown>
</template>

<script>
export default {
  props: {
    align: {
      type: String,
      default: "right"
    },
    icon: {
      type: String,
      default: "dots",
    },
    options: {
      type: [Array, Function],
      default() {
        return []
      }
    },
    text: {
      type: [Boolean, String],
      default: false
    },
    theme: {
      type: String,
      default: "dark",
    },
  },
  computed: {
    single() {
      return Array.isArray(this.options) && this.options.length === 1;
    }
  },
  methods: {
    onOption(option, item, itemIndex) {
      this.$emit("option", option, item, itemIndex);
    }
  }
}
</script>

<style lang="scss">
.k-options-dropdown {
  height: 38px;
}
.k-options-dropdown-toggle {
  display: block;
  min-width: 38px;
  height: 38px;
  padding: 0 .75rem;
}
</style>
