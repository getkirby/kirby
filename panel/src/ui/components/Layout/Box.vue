<template>
  <div :data-theme="theme" class="kirby-box" v-on="$listeners">
    <slot>
      <kirby-text v-html="text" />
    </slot>
  </div>
</template>

<script>
export default {
  props: {
    theme: String,
    text: String
  }
};
</script>

<style lang="scss">

.kirby-box {
  background: $color-inset;
  border-radius: $border-radius;
  padding: .375rem .75rem;
  line-height: 1.25rem;
  border: 1px solid $color-border;
  word-wrap: break-word;
}
.kirby-box[data-theme="negative"] {
  background: $color-negative-outline;
  border: 1px solid $color-negative-border;
  color: $color-black;
}
.kirby-box[data-theme="positive"] {
  background: $color-positive-outline;
  border: 1px solid $color-positive-border;
  color: $color-black;
}
.kirby-box[data-theme="code"] {
  background: $color-dark;
  border: 1px solid $color-black;
  color: $color-light;
  font-family: "Input", "Menlo", monospace;
  font-size: $font-size-small;
  line-height: 1.5;
}
.kirby-box[data-theme="button"] {
  padding: 0;
}
.kirby-box[data-theme="button"] .kirby-button {
  padding: 0 .75rem;
  height: 2.25rem;
  width: 100%;
  display: flex;
  align-items: center;
  line-height: 2rem;
  text-align: left;
}
</style>
