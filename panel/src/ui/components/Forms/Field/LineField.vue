<template>
  <hr class="kirby-line-field" >
</template>

<style lang="scss">
.kirby-line-field {
  position: relative;
  border: 0;
  height: 3rem;
  width: auto;

  &::after {
    position: absolute;
    content: "";
    top: 50%;
    margin-top: -1px;
    left: 0;
    right: 0;
    height: 1px;
    background: $color-border;
  }
}
</style>
