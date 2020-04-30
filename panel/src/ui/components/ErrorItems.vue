<template>
  <div class="k-error-items relative">
    <k-empty-items
      v-bind="loader"
      :layout="layout"
      :limit="limit"
    />
    <div class="k-error-items-message absolute flex items-center justify-center rounded-sm">
      <p class="p-3 bg-white rounded-sm"><slot /></p>
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
  right: 0;
  bottom: 0;
  left: 0;
  background: rgba($color-red-300, .5);
  z-index: z-index(dropzone);

  > span {

  }
}
</style>
