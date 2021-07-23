<template>
  <hr class="k-line-field">
</template>

<script>
/**
 * @example <k-line-field />
 */
export default {}
</script>

<style>
.k-line-field {
  position: relative;
  border: 0;
  height: 3rem;
  width: auto;
}
.k-line-field::after {
  position: absolute;
  content: "";
  inset-block-start: 50%;
  margin-block-start: -1px;
  inset-inline: 0;
  height: 1px;
  background: var(--color-border);
}
</style>
