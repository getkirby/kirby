<template>
  <div class="k-writer-toolbar">
    <k-dropdown v-if="hasVisibleButtons" @mousedown.native.prevent>
      <k-button
        :icon="activeButton.icon || 'title'"
        :class="{
          'k-writer-toolbar-button k-writer-toolbar-nodes': true,
          'k-writer-toolbar-button-active': !!activeButton
        }"
        @click="$refs.nodes.toggle()"
      />
      <k-dropdown-content ref="nodes">
        <template v-for="(node, nodeType) in nodeButtons">
          <k-dropdown-item
            :key="nodeType"
            :current="isButtonCurrent(node)"
            :disabled="isButtonDisabled(node)"
            :icon="node.icon"
            @click="command(node.command || nodeType)"
          >
            {{ node.label }}
          </k-dropdown-item>
          <hr v-if="needDividerAfterNode(node)" :key="nodeType + '-divider'" />
        </template>
      </k-dropdown-content>
    </k-dropdown>

    <k-button
      v-for="(mark, markType) in markButtons"
      :key="markType"
      :class="{
        'k-writer-toolbar-button': true,
        'k-writer-toolbar-button-active': activeMarks.includes(markType)
      }"
      :icon="mark.icon"
      :tooltip="mark.label"
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
    activeNodeAttrs: {
      type: [Array, Object],
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
    },
    isParagraphNodeHidden: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    activeButton() {
      return (
        Object.values(this.nodeButtons).find((button) =>
          this.isButtonActive(button)
        ) || false
      );
    },
    hasVisibleButtons() {
      const nodeButtons = Object.keys(this.nodeButtons);

      return (
        nodeButtons.length > 1 ||
        (nodeButtons.length === 1 &&
          nodeButtons.includes("paragraph") === false)
      );
    },
    markButtons() {
      return this.buttons("mark");
    },
    nodeButtons() {
      let nodeButtons = this.buttons("node");

      // remove the paragraph when certain nodes are requested to be loaded
      if (this.isParagraphNodeHidden === true && nodeButtons.paragraph) {
        delete nodeButtons.paragraph;
      }

      return nodeButtons;
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

      sorting.forEach((buttonName) => {
        if (available[buttonName]) {
          buttons[buttonName] = available[buttonName];
        }
      });

      return buttons;
    },
    command(command, ...args) {
      this.$emit("command", command, ...args);
    },
    isButtonActive(button) {
      // since the list element also contains a paragraph,
      // it is confused whether the list element is an active node
      // this solves the issue
      if (button.name === "paragraph") {
        return (
          this.activeNodes.length === 1 &&
          this.activeNodes.includes(button.name)
        );
      }

      let isActiveNodeAttr = true;

      if (button.attrs) {
        const activeNodeAttrs = Object.values(this.activeNodeAttrs).find(
          (node) => JSON.stringify(node) === JSON.stringify(button.attrs)
        );

        isActiveNodeAttr = Boolean(activeNodeAttrs || false);
      }

      return (
        isActiveNodeAttr === true && this.activeNodes.includes(button.name)
      );
    },
    isButtonCurrent(node) {
      if (this.activeButton) {
        return this.activeButton.id === node.id;
      }

      return false;
    },
    isButtonDisabled(node) {
      if (this.activeButton?.when) {
        const when = this.activeButton.when;
        return when.includes(node.name) === false;
      }

      return false;
    },
    needDividerAfterNode(node) {
      let afterNodes = ["paragraph"];
      let nodeButtons = Object.keys(this.nodeButtons);

      // add divider if list node available
      if (
        nodeButtons.includes("bulletList") ||
        nodeButtons.includes("orderedList")
      ) {
        afterNodes.push("h6");
      }

      return afterNodes.includes(node.id);
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
  transform: translateX(-50%) translateY(-0.75rem);
  z-index: calc(var(--z-dropdown) + 1);
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
  background: rgba(255, 255, 255, 0.15);
}
.k-writer-toolbar-button.k-writer-toolbar-button-active {
  color: var(--color-blue-300);
}
.k-writer-toolbar-button.k-writer-toolbar-nodes {
  width: auto;
  padding: 0 0.75rem;
}
.k-writer-toolbar .k-dropdown + .k-writer-toolbar-button {
  border-inline-start: 1px solid var(--color-gray-700);
}
.k-writer-toolbar-button.k-writer-toolbar-nodes::after {
  content: "";
  margin-inline-start: 0.5rem;
  border-top: 4px solid var(--color-white);
  border-inline: 4px solid transparent;
}
.k-writer-toolbar .k-dropdown-content {
  color: var(--color-black);
  background: var(--color-white);
  margin-top: 0.5rem;
}
.k-writer-toolbar .k-dropdown-content .k-dropdown-item[aria-current] {
  color: var(--color-focus);
  font-weight: 500;
}
</style>
