<template>
  <draggable
    :element="element"
    :list="list"
    :options="dragOptions"
    class="k-draggable"
    v-on="listeners"
  >
    <slot />
    <slot slot="footer" name="footer" />
  </draggable>
</template>

<script>
import Draggable from "vuedraggable";

export default {
  components: {
    draggable: Draggable
  },
  props: {
    element: String,
    handle: [String, Boolean],
    list: [Array, Object],
    options: Object
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        start: () => {
          this.$store.dispatch("drag", {});

          if (this.$listeners.start) {
            this.$listeners.start();
          }
        },
        end: () => {
          this.$store.dispatch("drag", null);

          if (this.$listeners.end) {
            this.$listeners.end();
          }
        }
      }
    };
  },
  computed: {
    dragOptions() {
      let handle = false;

      if (this.handle === true) {
        handle = ".k-sort-handle";
      } else {
        handle = this.handle;
      }

      return {
        fallbackClass: "k-sortable-fallback",
        fallbackOnBody: true,
        forceFallback: true,
        ghostClass: "k-sortable-ghost",
        handle: handle,
        scroll: document.querySelector(".k-panel-view"),
        ...this.options
      };
    }
  }
};
</script>
