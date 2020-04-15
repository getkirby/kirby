<template>
  <k-button
    v-if="single && options[0]"
    :icon="options[0].icon"
    :tooltip="options[0].text"
    class="k-options-dropdown-toggle"
    @click="$emit('option', options[0].click)"
  />
  <k-dropdown
    v-else-if="options.length"
    class="k-options-dropdown"
  >
    <k-button
      :icon="icon"
      :tooltip="$t('options')"
      class="k-options-dropdown-toggle"
      @click="$refs.options.toggle()"
    />
    <k-dropdown-content
      ref="options"
      :align="align"
      :options="options"
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
    }
  },
  computed: {
    single() {
      return Array.isArray(this.options) && this.options.length === 1;
    }
  },
  methods: {
    onOption(option) {
      this.$emit("option", option);
    }
  }
}
</script>

<style lang="scss">
.k-options-dropdown {
  display: flex;
  width: 38px;
  height: 38px;
  align-items: center;
  justify-content: center;
}
.k-options-dropdown-toggle {
  width: 38px;
  height: 38px;
}
</style>
