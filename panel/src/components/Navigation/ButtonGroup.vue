<template>
  <div class="k-button-group">
    <slot />
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
export default {}
</script>

<style>
.k-button-group {
  --button-group-padding-horizontal: .75rem;
  --button-group-padding-vertical: 1rem;
  --button-group-line-height: 1rem;

  font-size: 0;
  margin-left: calc(var(--button-group-padding-horizontal) * -1);
  margin-right: calc(var(--button-group-padding-horizontal) * -1);
}
.k-button-group > .k-dropdown {
  height: calc(var(--button-group-line-height) + (var(--button-group-padding-vertical) * 2));
  display: inline-block;
}
.k-button-group > .k-dropdown > .k-button,
.k-button-group > .k-button {
  padding: var(--button-group-padding-vertical) var(--button-group-padding-horizontal);
  line-height: var(--button-group-line-height);
}
.k-button-group .k-dropdown-content {
  top: calc(100% + 1px);
  margin: 0 var(--button-group-padding-horizontal);
}
</style>
