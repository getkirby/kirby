<template>
  <div class="k-button-group">
    <slot v-if="$slots.default" />
    <k-button
      v-for="(button, index) in buttons"
      v-else
      :key="index"
      v-bind="button"
    />
  </div>
</template>

<script>
/**
 * The Button Group should always be used when two or more buttons are positioned next to each other. The Button Group takes care of consistent margins between buttons.
 *
 * @example <k-button-group>
  <k-button icon="edit">Edit</k-button>
  <k-button icon="trash">Delete</k-button>
</k-button-group>
 */
export default {
  props: {
    /**
     * Either pass the buttons as default slot
     * or as an array to this prop
     */
    buttons: Array
  }
};
</script>

<style>
.k-button-group {
  --padding-x: 0.75rem;
  --padding-y: 1rem;
  --line-height: 1rem;

  font-size: 0;
  margin: 0 calc(var(--padding-x) * -1);
}
.k-button-group > .k-dropdown {
  height: calc(var(--line-height) + (var(--padding-y) * 2));
  display: inline-block;
}
.k-button-group > .k-dropdown > .k-button,
.k-button-group > .k-button {
  padding: var(--padding-y) var(--padding-x);
  line-height: var(--line-height);
}
.k-button-group .k-dropdown-content {
  top: calc(100% + 1px);
  margin: 0 var(--padding-x);
}
</style>
