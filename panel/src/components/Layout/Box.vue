<template>
  <div :data-theme="theme" class="k-box" v-on="$listeners">
    <!-- @slot Use instead of `text` prop -->
    <slot>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-if="html" v-html="text" />
      <k-text v-else>
        {{ text }}
      </k-text>
    </slot>
  </div>
</template>

<script>
/**
 * The box component is a multi-purpose box with text. You can use it as a foundation for empty state displays or anything else that needs to be displayed in a box. It comes with several pre-defined styles …
 * 
 * @example <k-box text="This is a nice box" theme="positive" />
 */
export default {
  props: {
    /**
     * Choose one of the pre-defined styles
     * @values none, code, button, positive, negative, notice, info, empty
     */
    theme: {
      type: String,
      default: "none"
    },
    /**
     * Text to display inside the box
     */
    text: String,
    /**
     * If set to `false`, the `text` is rendered as text only
     * @todo Switch default value to `false` in 3.6.0
     */
    html: {
      type: Boolean,
      default: true
    }
  }
};
</script>

<style lang="scss">
.k-box {
  word-wrap: break-word;
  font-size: $text-sm;
}
.k-box:not([data-theme="none"]) {
  background: lighten($color-light-grey, 25%);
  border-radius: $rounded-xs;
  padding: 0.375rem 0.75rem;
  line-height: 1.25rem;
  border-left: 2px solid $color-light-grey;
  padding: 0.5rem 1.5rem;
}
.k-box[data-theme="code"] {
  background: $color-gray-900;
  border: 1px solid $color-black;
  color: $color-light;
  font-family: "Input", "Menlo", monospace;
  font-size: $text-sm;
  line-height: 1.5;
}
.k-box[data-theme="button"] {
  padding: 0;
}
.k-box[data-theme="button"] .k-button {
  padding: 0 0.75rem;
  height: 2.25rem;
  width: 100%;
  display: flex;
  align-items: center;
  line-height: 2rem;
  text-align: left;
}

.k-box[data-theme="positive"] {
  background: lighten($color-positive-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-positive-on-dark;
  padding: 0.5rem 1.5rem;
}
.k-box[data-theme="negative"] {
  background: lighten($color-negative-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-negative-on-dark;
  padding: 0.5rem 1.5rem;
}
.k-box[data-theme="notice"] {
  background: lighten($color-notice-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-notice-on-dark;
  padding: 0.5rem 1.5rem;
}
.k-box[data-theme="info"] {
  background: lighten($color-focus-on-dark, 25%);
  border: 0;
  border-left: 2px solid $color-focus-on-dark;
  padding: 0.5rem 1.5rem;
}

.k-box[data-theme="empty"] {
  text-align: center;
  border-left: 0;
  padding: 3rem 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background: $color-background;
  border-radius: $rounded-xs;
  color: $color-gray-600;
  border: 1px dashed $color-border;
}
.k-box[data-theme="empty"] .k-icon {
  margin-bottom: 0.5rem;
  color: $color-light-grey;
}
.k-box[data-theme="empty"] p {
  color: $color-gray-600;
}
</style>
