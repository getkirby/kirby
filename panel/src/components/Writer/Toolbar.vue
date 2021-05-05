<template>
  <div class="k-writer-toolbar">
    <k-dropdown v-if="Object.keys(nodeButtons).length > 1 && activeNode" @mousedown.native.prevent>
      <k-button
        :icon="activeNode.icon"
        class="k-writer-toolbar-button k-writer-toolbar-nodes"
        @click="$refs.nodes.toggle()"
      />
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
    </k-dropdown>

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
    activeNodes: {
      type: Array,
      default() {
        return [];
      }
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
    activeNode() {

      const buttonKey = Object.keys(this.nodeButtons).find(buttonKey => this.activeNodes.includes(buttonKey));

      if (buttonKey) {
        return this.nodeButtons[buttonKey];
      }

      return false;
    },
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

<style>
.k-writer-toolbar {
  position: absolute;
  display: flex;
  background: var(--color-black);
  height: 30px;
  transform: translateX(-50%) translateY(-.75rem);
  z-index: var(--z-dropdown) + 1;
  box-shadow: var(--shadow);
  color: var(--color-white);
  border-radius: var(--rounded);
}
.k-writer-toolbar-button.k-button {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 30px;
  width: 30px;
  font-size: var(--text-sm) !important;
  color: currentColor;
  line-height: 1;
}
.k-writer-toolbar-button.k-button:hover {
  background: rgba(255, 255, 255, .15);
}
.k-writer-toolbar-button.k-writer-toolbar-button-active {
  color: var(--color-blue-300);
}
.k-writer-toolbar-button.k-writer-toolbar-nodes {
  width: auto;
  padding: 0 .75rem;
}
.k-writer-toolbar .k-dropdown + .k-writer-toolbar-button {
  border-left: 1px solid var(--color-gray-700);
}
.k-writer-toolbar-button.k-writer-toolbar-nodes::after {
  content: "";
  margin-left: .5rem;
  border-top: 4px solid var(--color-white);
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
}
.k-writer-toolbar .k-dropdown-content {
  color: var(--color-black);
  background: var(--color-white);
  margin-top: .5rem;
}
</style>
