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
.k-text ol,
.k-text ul {
  margin-left: 1rem;
  list-style-position: inside;
}
.k-text li {
  list-style: inherit;
}
.k-text p,
.k-text > ol,
.k-text > ul {
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
  text-align: right;
}

.k-text[data-size="tiny"] {
  font-size: $text-xs;
}
.k-text[data-size="small"] {
  font-size: $text-sm;
}
.k-text[data-size="medium"] {
  font-size: $text-base;
}
.k-text[data-size="large"] {
  font-size: $text-xl;
}

.k-text[data-theme="help"] {
  font-size: $text-sm;
  color: $color-gray-600;
  line-height: 1.25rem;
}

.k-dialog-body .k-text {
  word-wrap: break-word;
}
</style>
