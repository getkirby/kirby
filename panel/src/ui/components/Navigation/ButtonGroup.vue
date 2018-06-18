<template>
  <div class="kirby-button-group">
    <slot />
  </div>
</template>

<style lang="scss">
$button-group-padding-horizontal: 0.75rem;
$button-group-padding-vertical: 1rem;
$button-group-line-height: 1rem;

.kirby-button-group {
  font-size: 0;
  margin-left: $button-group-padding-horizontal * -1;
  margin-right: $button-group-padding-horizontal * -1;
}
.kirby-button-group > .kirby-dropdown {
  height: $button-group-line-height + ($button-group-padding-vertical * 2);
  display: inline-block;
}
.kirby-button-group > .kirby-dropdown > .kirby-button,
.kirby-button-group > .kirby-button {
  padding: $button-group-padding-vertical $button-group-padding-horizontal;
  line-height: $button-group-line-height;
}
.kirby-button-group .kirby-dropdown-content {
  top: calc(100% + 1px);
  margin: 0 $button-group-padding-horizontal;
}
</style>
