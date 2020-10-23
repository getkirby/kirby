<template>
  <div class="k-writer-toolbar">
    <!-- <k-dropdown @mousedown.native.prevent>
      <k-button class="k-writer-toolbar-button k-writer-toolbar-nodes" @click="$refs.nodes.toggle()" icon="text">Paragraph</k-button>
      <k-dropdown-content ref="nodes">
        <k-dropdown-item
          v-for="(node, nodeType) in nodeButtons"
          :key="nodeType"
          :icon="node.icon"
          @click="command(node.command || nodeType)"
        >
          {{ node.label }}
        </k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown> -->

    <k-button
      v-for="(mark, markType) in markButtons"
      :key="markType"
      :class="{'k-writer-toolbar-button': true, 'k-writer-toolbar-button-active': activeMarks.includes(markType)}"
      :icon="mark.icon"
      @mousedown.prevent="command(mark.command || markType)"
    />
  </div>
</template>

<script>
export default {
  props: {
    activeMarks: {
      type: Array,
      default() {
        return [];
      }
    },
    activeNode: {
      type: [String, Boolean]
    },
    editor: {
      type: Object,
      required: true
    },
    marks: {
      type: Array
    }
  },
  computed: {
    markButtons() {
      return this.buttons("mark");
    },
    nodeButtons() {
      return this.buttons("node");
    }
  },
  methods: {
    buttons(type) {
      const available = this.editor.buttons(type);
      let sorting = this.sorting;

      if (sorting === false || Array.isArray(sorting) === false) {
        sorting = Object.keys(available);
      }

      let buttons = {};

      sorting.forEach(buttonName => {
        if (available[buttonName]) {
          buttons[buttonName] = available[buttonName];
        }
      });

      return buttons;
    },
    command(command, ...args) {
      this.$emit("command", command, ...args);
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

.k-writer-toolbar-nodes {
  border-right: 1px solid $color-gray-700;
}

</style>
