<template>
  <component
    :is="element"
    :data-layout="layout"
    :type="element === 'button' ? 'button' : false"
    class="k-empty"
    v-on="$listeners"
  >
    <k-icon
      v-if="icon"
      :type="icon"
    />
    <p><slot /></p>
  </component>
</template>

<script>
/**
 * Whenever you have to deal with an "empty" state, such as an empty list or a search without results, you can use the `k-empty` component to make it a bit nicer. The component combines an icon with text in a wrapper box.
 * @example <k-empty icon="image">No images yet</k-empty>
 */
export default {
  props: {
    /**
     * Text to show inside the box
     */
    text: String,
    /**
     * Icon to show inside the box
     */
    icon: String,
    /**
     * Layout for the box
     * @types list, cards
     */
    layout: {
      type: String,
      default: "list"
    }
  },
  computed: {
    element() {
      return this.$listeners["click"] !== undefined ? "button" : "div";
    }
  }
};
</script>

<style>
/* global styles */
.k-empty {
  display: flex;
  align-items: stretch;
  border-radius: var(--rounded-xs);
  color: var(--color-gray-600);
  border: 1px dashed var(--color-border);
}
button.k-empty {
  width: 100%;
}
button.k-empty:focus {
  outline: none;
}
.k-empty p {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
}
.k-empty > .k-icon {
  color: var(--color-gray-500);
}

/* layout:cards */
.k-empty[data-layout="cards"] {
  text-align: center;
  padding: 1.5rem;
  justify-content: center;
  flex-direction: column;
}
.k-empty[data-layout="cards"] .k-icon {
  margin-bottom: 1rem;
}
.k-empty[data-layout="cards"] .k-icon svg {
  width: 2rem;
  height: 2rem;
}

/* layout:list */
.k-empty[data-layout="list"] {
  min-height: 38px;
}
.k-empty[data-layout="list"] > .k-icon {
  width: 36px;
  min-height: 36px;
  border-right: 1px solid rgba(0, 0, 0, .05);
}
.k-empty[data-layout="list"] > p {
  line-height: 1.25rem;
  padding: .5rem .75rem;
}
</style>
