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
/**
 * The Text component is a container for all multi-line text with additional formats.
 * @example <k-text>
  <b>Lorem</b> <a href="#">ipsum</a> <i>dolor</i> …
</k-text>
 */
export default {
  props: {
    /**
     * Changes the text alignment
     * @values left, centre, right 
     */
    align: String,
    /**
     * Changes the font size of the text
     * @values tiny, small, medium, large, huge
     */
    size: String,
    /**
     * Changes the layout/design of the text
     * @values help
     */
    theme: String
  }
}
</script>

<style>
.k-text {
  line-height: 1.5em;
}
.k-text ol,
.k-text ul {
  margin-left: 1rem;
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
  font-size: var(--text-xs);
}
.k-text[data-size="small"] {
  font-size: var(--text-sm);
}
.k-text[data-size="medium"] {
  font-size: var(--text-base);
}
.k-text[data-size="large"] {
  font-size: var(--text-xl);
}

.k-text[data-theme="help"] {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  line-height: 1.25rem;
}

.k-dialog-body .k-text {
  word-wrap: break-word;
}
</style>
