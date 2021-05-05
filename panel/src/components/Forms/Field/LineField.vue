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
  top: 50%;
  margin-top: -1px;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--color-border);
}
</style>
