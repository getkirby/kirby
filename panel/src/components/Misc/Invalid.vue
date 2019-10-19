<style lang="scss">
[data-invalid] {
  border: 1px solid $color-negative-outline;
  box-shadow: $color-negative-outline 0 0 3px 2px;
}

[data-invalid]:focus-within {
  border: $field-input-invalid-focus-border !important;
  box-shadow: $color-negative-outline 0 0 0 2px !important;
}
</style>
