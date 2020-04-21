<template>
  <span
    ref="button"
    class="k-tag"
    tabindex="0"
    @keydown.delete.prevent="remove"
  >
    <span class="k-tag-text"><slot /></span>
    <span
      v-if="removable"
      class="k-tag-toggle"
      @click="remove"
    >&times;</span>
  </span>
</template>

<script>
export default {
  props: {
    removable: Boolean,
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
  position: relative;
  font-size: $text-sm;
  line-height: 1;
  cursor: pointer;
  background-color: $color-dark;
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
  width: 2rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border-left: 1px solid rgba(255, 255, 255, 0.15);
}
.k-tag-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}
</style>
