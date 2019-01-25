<template>
  <div class="k-button-group">
    <slot />
  </div>
</template>

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
