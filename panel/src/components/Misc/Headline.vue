<template>
  <component
    :is="tag"
    :data-theme="theme"
    :data-size="size"
    class="k-headline"
    v-on="$listeners"
  >
    <k-link v-if="link" :to="link">
      <slot />
    </k-link>
    <slot v-else />
  </component>
</template>

<script>
/**
 * @example <k-headline>This is a nice headline</k-headline>
 */
export default {
  props: {
    /**
     * Set a link for the headline. The link can be absolute or relative.
     */
    link: String,
    /**
     * Sets the text size
     * @values tiny, small, medium, large, huge
     */
    size: {
      type: String
    },
    /**
     * Sets the html tag for the headline element
     */
    tag: {
      type: String,
      default: "h2"
    },
    /**
     * @values positive, negative
     */
    theme: {
      type: String
    }
  }
};
</script>

<style>
.k-headline {
  font-size: var(--text-base);
  font-weight: var(--font-bold);
  line-height: 1.5em;
}
.k-headline[data-size="small"] {
  font-size: var(--text-sm);
}
.k-headline[data-size="large"] {
  font-size: var(--text-xl);
  font-weight: var(--font-normal);
}
@media screen and (min-width: 65em) {
  .k-headline[data-size="large"] {
    font-size: var(--text-2xl);
  }
}
.k-headline[data-size="huge"] {
  font-size: var(--text-2xl);
  line-height: 1.15em;
}
@media screen and (min-width: 65em) {
  .k-headline[data-size="huge"] {
    font-size: var(--text-3xl);
  }
}
.k-headline[data-theme="negative"] {
  color: var(--color-negative);
}
.k-headline[data-theme="positive"] {
  color: var(--color-positive);
}
.k-headline abbr {
  color: var(--color-gray-500);
  padding-left: .25rem;
  text-decoration: none;
}
</style>
