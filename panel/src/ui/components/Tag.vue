<template>
  <span
    ref="button"
    class="k-tag"
    tabindex="0"
    @keydown.delete.prevent="remove"
  >
    <span class="k-tag-text"><slot /></span>
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
        /**
         * This event is emitted when the remove button is being
         * clicked or the tag is focussed and the delete key is entered.
         */
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
  position: relative;
  font-size: $text-sm;
  line-height: 1;
  cursor: pointer;
  background-color: $color-black;
  color: $color-light;
  border-radius: $rounded-sm;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  user-select: none;
  height: 1.5rem;
}
.k-tag:focus {
  outline: 0;
  background-color: $color-focus;
  border-color: $color-focus;
  color: #fff;
}
.k-tag-text {
  padding: 0 .75rem;
}
.k-tag-toggle {
  color: rgba(255, 255, 255, 0.7);
  width: calc(2rem - 1px);
  height: 100%;
  border-left: 1px solid rgba(255, 255, 255, 0.15);
}
</style>
