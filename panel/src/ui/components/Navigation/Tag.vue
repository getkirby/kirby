<template>
  <span
    ref="button"
    :data-size="size"
    class="k-tag"
    tabindex="0"
    @keydown.delete.prevent="remove"
  >
    <span class="k-tag-text"><slot /></span>
    <span v-if="removable" class="k-tag-toggle" @click="remove">&times;</span>
  </span>
</template>

<script>
export default {
  props: {
    removable: Boolean,
    size: String
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
  font-size: $font-size-small;
  line-height: 1;
  cursor: pointer;
  background-color: $color-dark;
  color: $color-light;
  border-radius: $border-radius;
  display: flex;
  align-items: center;
  justify-content: space-between;
  user-select: none;
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
