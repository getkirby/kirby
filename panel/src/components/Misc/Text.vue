<template>
  <div
    :data-align="align"
    :data-size="size"
    :data-theme="theme"
    class="k-text"
  >
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    align: String,
    size: String,
    theme: String
  }
}
</script>

<style lang="scss">
.k-text {
  line-height: 1.5em;
}
.k-text p {
  margin-bottom: 1.5em;
}
.k-text a {
  text-decoration: underline;
}
.k-text > *:last-child {
  margin-bottom: 0;
}

.k-text[data-align="center"] {
  text-align: center;
}
.k-text[data-align="right"] {
  text-align: center;
}

.k-text[data-size="tiny"] {
  font-size: $font-size-tiny;
}
.k-text[data-size="small"] {
  font-size: $font-size-small;
}
.k-text[data-size="medium"] {
  font-size: $font-size-medium;
}
.k-text[data-size="large"] {
  font-size: $font-size-large;
}

.k-text[data-theme="help"] {
  font-size: $font-size-small;
  color: $color-dark-grey;
  line-height: 1.5rem;
}
</style>
