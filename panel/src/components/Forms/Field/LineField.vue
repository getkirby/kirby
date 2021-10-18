<template>
  <hr class="k-line-field" :data-size="size">
</template>

<script>
/**
 * @example <k-line-field />
 */
export default {
  inheritAttrs: false,
  props: {
    size: String,
  }
}
</script>

<style>
.k-line-field {
  position: relative;
  border: 0;
  height: 3rem;
  width: auto;
}
.k-line-field[data-size="small"] {
  height: .5rem;
}
.k-line-field[data-size="medium"] {
  height: 1rem;
}
.k-line-field[data-size="large"] {
  height: 2rem;
}
.k-line-field[data-size="huge"] {
  height: 5rem;
}
.k-line-field::after {
  position: absolute;
  content: "";
  top: 50%;
  margin-top: -1px;
  inset-inline: 0;
  height: 1px;
  background: var(--color-border);
}
</style>
