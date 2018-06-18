<template>
  <span
    ref="button"
    :data-size="size"
    class="kirby-tag"
    tabindex="0"
    @keydown.delete.prevent="remove"
  >
    <span class="kirby-tag-text"><slot /></span>
    <span v-if="removable" class="kirby-tag-toggle" @click="remove">&times;</span>
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
      console.log("yay");
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
.kirby-tag {
  position: relative;
  font-size: $font-size-small;
  line-height: 1;
  cursor: pointer;
  background-color: $color-dark;
  color: $color-light;
  border-radius: $border-radius;
  display: flex;
  align-items: center;
}
.kirby-tag:focus {
  outline: 0;
  background-color: $color-focus;
  border-color: $color-focus;
  color: #fff;
}
.kirby-tag-text {
  padding: 0 .75rem;
}
.kirby-tag-toggle {
  color: rgba(255, 255, 255, 0.7);
  width: 2rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  border-left: 1px solid rgba(255, 255, 255, 0.15);
}
.kirby-tag-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}
</style>
