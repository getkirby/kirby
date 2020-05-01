<template>
  <div class="k-error-items relative">
    <k-empty-items
      v-bind="loader"
      :layout="layout"
      :limit="limit"
    />
    <div class="k-error-items-message absolute flex items-center justify-center">
      <p class="bg-red-light shadow-lg rounded-sm py-2 px-3 text-sm flex items-center">
        <k-icon type="alert" class="mr-2" /><slot />
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
    },
    /**
     * Settings for the empty loading state.
     * See EmptyItems for available options
     */
    loader: {
      type: Object,
      default() {
        return {}
      }
    },
    limit: Number
  }
};
</script>

<style lang="scss">
.k-error-items-message {
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  z-index: 10;
}
.k-error-items .k-empty-item .k-item-title::after,
.k-error-items .k-empty-item .k-item-info::after {
  animation: none;
  background: $color-gray-200;
}
</style>
