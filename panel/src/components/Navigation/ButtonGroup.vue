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

<style lang="scss">
$button-group-padding-horizontal: 0.75rem;
$button-group-padding-vertical: 1rem;
$button-group-line-height: 1rem;

.k-button-group {
  font-size: 0;
  margin-left: $button-group-padding-horizontal * -1;
  margin-right: $button-group-padding-horizontal * -1;
}
.k-button-group > .k-dropdown {
  height: $button-group-line-height + ($button-group-padding-vertical * 2);
  display: inline-block;
}
.k-button-group > .k-dropdown > .k-button,
.k-button-group > .k-button {
  padding: $button-group-padding-vertical $button-group-padding-horizontal;
  line-height: $button-group-line-height;
}
.k-button-group .k-dropdown-content {
  top: calc(100% + 1px);
  margin: 0 $button-group-padding-horizontal;
}
</style>
