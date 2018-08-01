<template>
  <div :data-theme="theme" class="k-box" v-on="$listeners">
    <slot>
      <k-text v-html="text" />
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

.k-box {
  background: lighten($color-light-grey, 25%);
  border-radius: $border-radius;
  padding: .375rem .75rem;
  line-height: 1.25rem;
  border-left: 2px solid $color-light-grey;
  padding: .5rem 1.5rem;
  word-wrap: break-word;
  font-size: $font-size-small;
}
.k-box[data-theme="code"] {
  background: $color-dark;
  border: 1px solid $color-black;
  color: $color-light;
  font-family: "Input", "Menlo", monospace;
  font-size: $font-size-small;
  line-height: 1.5;
}
.k-box[data-theme="button"] {
  padding: 0;
}
.k-box[data-theme="button"] .k-button {
  padding: 0 .75rem;
  height: 2.25rem;
  width: 100%;
  display: flex;
  align-items: center;
  line-height: 2rem;
  text-align: left;
}

/** TODO: refactor this and remove the stuff above if possible **/
.k-box[data-theme="positive"] {
  background: lighten($color-positive-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-positive-on-dark;
  padding: .5rem 1.5rem;
}
.k-box[data-theme="negative"] {
  background: lighten($color-negative-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-negative-on-dark;
  padding: .5rem 1.5rem;
}
.k-box[data-theme="notice"] {
  background: lighten($color-notice-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-notice-on-dark;
  padding: .5rem 1.5rem;
}
.k-box[data-theme="info"] {
  background: lighten($color-focus-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-focus-on-dark;
  padding: .5rem 1.5rem;
}
</style>
