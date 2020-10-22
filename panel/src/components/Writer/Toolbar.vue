<template>
  <div class="k-writer-toolbar">
    <k-button
      v-for="(mark, markType) in sortedButtons"
      :key="markType"
      :class="{'k-writer-toolbar-button': true, 'k-writer-toolbar-button-active': marks.includes(markType)}"
      :icon="mark.icon"
      @mousedown.prevent="$emit('command', mark.command || markType)"
    />
  </div>
</template>

<script>
export default {
  props: {
    buttons: {
      type: Object,
      default() {
        return {}
      }
    },
    marks: {
      type: Array,
      default() {
        return [];
      }
    },
    sorting: Array
  },
  computed: {
    sortedButtons() {
      if (!this.sorting) {
        return this.buttons;
      }

      let buttons = {};

      this.sorting.forEach(buttonName => {
        buttons[buttonName] = this.buttons[buttonName];
      });

      return buttons;
    }
  }
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
}
.k-writer-toolbar-button.k-button {
  display: flex;
  align-items: center;
  height: 36px;
  padding: 0 .5rem;
  font-size: $text-sm !important;
  color: currentColor;
  line-height: 1;
}
.k-writer-toolbar-button.k-writer-toolbar-button-active {
  color: $color-blue-300;
}
</style>
