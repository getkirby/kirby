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
 * The `<k-box>` component is a multi-purpose
 * box with text. You can use it as a foundation
 * for empty state displays or anything else
 * that needs to be displayed in a box. It comes
 * with several pre-defined styles …
 * @public
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
     * If set to `true`, the `text` is rendered as HTML code, otherwise as plain text
     */
    html: {
      type: Boolean,
      default: false
    }
  }
};
</script>

<style>
.k-box {
  word-wrap: break-word;
  font-size: var(--text-sm);
}
.k-box:not([data-theme="none"]) {
  background: var(--color-gray-300);
  border-radius: var(--rounded-xs);
  line-height: 1.25rem;
  border-inline-start: 2px solid var(--color-gray-500);
  padding: 0.5rem 1.5rem;
}
.k-box[data-theme="code"] {
  background: var(--color-gray-900);
  border: 1px solid var(--color-black);
  color: var(--color-light);
  font-family: "Input", "Menlo", monospace;
  font-size: var(--text-sm);
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
  text-align: start;
}

.k-box[data-theme="positive"],
.k-box[data-theme="negative"],
.k-box[data-theme="notice"],
.k-box[data-theme="info"] {
  border: 0;
  border-inline-start-color: var(--theme-light);
  background: var(--theme-bg);
}

.k-box[data-theme="empty"] {
  text-align: center;
  border-inline-start: 0;
  padding: 3rem 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background: var(--color-background);
  color: var(--color-gray-600);
  border: 1px dashed var(--color-border);
}
.k-box[data-theme="empty"] .k-icon {
  margin-bottom: 0.5rem;
  color: var(--color-gray-500);
}
.k-box[data-theme="empty"] p {
  color: var(--color-gray-600);
}
</style>
