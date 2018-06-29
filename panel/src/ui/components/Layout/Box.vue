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
  background: lighten($color-light-grey, 25%);
  border-radius: $border-radius;
  padding: .375rem .75rem;
  line-height: 1.25rem;
  border-left: 2px solid $color-light-grey;
  padding: .5rem 1.5rem;
  word-wrap: break-word;
  font-size: $font-size-small;
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

/** TODO: refactor this and remove the stuff above if possible **/
.kirby-box[data-theme="positive"] {
  background: lighten($color-positive-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-positive-on-dark;
  padding: .5rem 1.5rem;
}
.kirby-box[data-theme="negative"] {
  background: lighten($color-negative-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-negative-on-dark;
  padding: .5rem 1.5rem;
}
.kirby-box[data-theme="notice"] {
  background: lighten($color-notice-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-notice-on-dark;
  padding: .5rem 1.5rem;
}
.kirby-box[data-theme="info"] {
  background: lighten($color-focus-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-focus-on-dark;
  padding: .5rem 1.5rem;
}
</style>
