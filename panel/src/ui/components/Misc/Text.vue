<template>
  <div
    :data-align="align"
    :data-size="size"
    :data-theme="theme"
    class="kirby-text"
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
.kirby-text {
  line-height: 1.5em;
}
.kirby-text p {
  margin-bottom: 1.5em;
}
.kirby-text a {
  border-bottom: 2px solid $color-border;
  transition: border-bottom-color 0.3s;
}
.kirby-text a:focus,
.kirby-text a:hover {
  border-bottom-color: $color-dark;
}
.kirby-text > *:last-child {
  margin-bottom: 0;
}

.kirby-text[data-align="center"] {
  text-align: center;
}
.kirby-text[data-align="right"] {
  text-align: center;
}

.kirby-text[data-size="tiny"] {
  font-size: $font-size-tiny;
}
.kirby-text[data-size="small"] {
  font-size: $font-size-small;
}
.kirby-text[data-size="medium"] {
  font-size: $font-size-medium;
}
.kirby-text[data-size="large"] {
  font-size: $font-size-large;
}

.kirby-text[data-theme="help"] {
  font-size: $font-size-small;
  color: $color-dark-grey;
  line-height: 1.5rem;
}
</style>
