<template>
  <span
    ref="button"
    class="k-tag relative inline-flex items-center justify-between text-sm cursor-pointer bg-black text-white rounded-sm"
    tabindex="0"
    @keydown.delete.prevent="remove"
  >
    <span class="k-tag-text px-3"><slot /></span>
    <k-button
      v-if="removable"
      class="k-tag-toggle"
      icon="cancel-small"
      @click="remove"
    />
  </span>
</template>

<script>
export default {
  props: {
    /**
     * Enables the remove button.
     */
    removable: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    remove() {
      if (this.removable) {
        this.$emit("remove");
      }
    },
    focus() {
      this.$refs.button.focus();
    }
  }
};
</script>

<style lang="scss">
.k-tag {
  line-height: 1;
  user-select: none;
  height: 1.5rem;
}
.k-tag:focus {
  outline: 0;
  background-color: $color-focus;
  border-color: $color-focus;
  color: #fff;
}
.k-tag-toggle {
  color: rgba(255, 255, 255, 0.7);
  width: calc(2rem - 1px);
  height: 100%;
  border-left: 1px solid rgba(255, 255, 255, 0.15);
}
</style>
