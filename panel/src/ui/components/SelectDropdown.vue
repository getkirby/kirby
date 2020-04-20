<template>
  <k-dropdown v-if="options.length">
    <k-button
      :icon="icon"
      @click="$refs.options.toggle()"
    >
      {{ before }} {{ currentOption.text }} {{ after }}
    </k-button>
    <k-dropdown-content
      ref="options"
      :options="options"
      @option="onOption"
    />
  </k-dropdown>
</template>

<script>
export default {
  props: {
    after: {
      type: String,
    },
    before: {
      type: String,
    },
    icon: {
      type: String,
    },
    options: {
      type: Array,
      default() {
        return []
      }
    }
  },
  computed: {
    currentOption() {
      return this.options.filter(option => option.current)[0] || this.options[0];
    }
  },
  methods: {
    onOption(key, option, optionIndex) {
      this.$emit("change", option, optionIndex);
    }
  }
}
</script>
