<template>
  <header
    class="k-header-bar flex items-center justify-between h-10"
  >
    <!-- Heading -->
    <component
      :is="element"
      :for="$attrs['for']"
      class="flex font-bold items-center"
    >
      <k-link v-if="link" :to="link">
        {{ text }}
        <abbr
          v-if="required"
          :title="$t('required')"
          class="text-gray ml-1"
        >
          *
        </abbr>
      </k-link>
      <template v-else>
        {{ text }}
        <abbr
          v-if="required"
          :title="$t('required')"
          class="text-gray ml-1"
        >
          *
        </abbr>
      </template>
    </component>

    <!-- Options -->
    <k-options-dropdown
      v-if="options"
      :icon="optionsIcon"
      :options="options"
      :text="optionsText"
      @option="onOption"
    />
  </header>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    element: {
      type: String,
      default: "h2"
    },
    link: {
      type: [Boolean, String],
      default: false,
    },
    options: {
      type: [Boolean, Array],
      default: false
    },
    optionsIcon: {
      type: [Boolean, String],
      default: "dots"
    },
    optionsText: {
      type: [Boolean, String],
      default: false
    },
    required: {
      type: Boolean,
      default: false
    },
    text: {
      type: String
    }
  },
  methods: {
    onOption(option, item, itemIndex) {
      this.$emit("option", option, item, itemIndex);
    }
  }
};
</script>

<style lang="scss">
.k-header-bar {
  height: 2.5rem;
}
.k-header-bar .k-link,
.k-header-bar .k-options-dropdown-toggle {
  height: 2.5rem;
  width: auto;
  display: flex;
  align-items: center;
  padding: .75rem;
}
.k-header-bar .k-link {
  margin-left: -.75rem;
}
.k-header-bar .k-options-dropdown-toggle {
  margin-right: -.75rem;
}
</style>
