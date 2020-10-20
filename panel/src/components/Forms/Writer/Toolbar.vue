<template>
  <div class="k-writer-toolbar">
    <k-button
      v-for="(mark, markType) in options"
      :key="markType"
      :class="{'k-writer-toolbar-button': true, 'k-writer-toolbar-button-active': marks.includes(markType)}"
      :icon="mark.spec.toolbar.icon"
      @mousedown.prevent="$emit('option', mark.spec.toolbar)"
    />
  </div>
</template>

<script>
export default {
  props: {
    marks: {
      type: Array,
      default() {
        return []
      }
    },
    options: Object
  },
};
</script>

<style lang="scss">
.k-writer-toolbar {
  position: absolute;
  display: flex;
  background: $color-black;
  height: 36px;
  transform: translateX(-50%) translateY(-.75rem);
  z-index: 1;
  box-shadow: $shadow;
  color: $color-white;
  border-radius: $rounded;
  line-height: 1;
}
.k-writer-toolbar-button.k-button {
  display: flex;
  align-items: center;
  height: 36px;
  padding: 0 .5rem;
  font-size: $text-sm;
  color: currentColor;
}
.k-writer-toolbar-button.k-writer-toolbar-button-active {
  color: $color-blue-200;
}
</style>
