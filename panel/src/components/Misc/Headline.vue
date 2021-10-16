<template>
  <component
    :is="tag"
    :data-theme="theme"
    :data-size="size"
    class="k-headline"
    v-on="$listeners"
  >
    <k-link v-if="link" :to="link">
      <!-- @slot Content/text of the headline -->
      <slot />
    </k-link>
    <slot v-else />
  </component>
</template>

<script>
/**
 * Use to display consistently sized headlines
 * @public

 * @example <k-headline>This is a nice headline</k-headline>
 */
export default {
  props: {
    /**
     * Sets a link for the headline.
     * The link can be absolute or relative.
     */
    link: String,
    /**
     * Alternate text sizes
     * @values small, large, huge
     */
    size: {
      type: String
    },
    /**
     * HTML tag for the headline element
     */
    tag: {
      type: String,
      default: "h2"
    },
    /**
     * Visual look of the headline
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
  --size: var(--text-base);
  font-size: var(--size);
  font-weight: var(--font-bold);
  line-height: 1.5em;
}
.k-headline[data-size="small"] {
  --size: var(--text-sm);
}
.k-headline[data-size="large"] {
  --size: var(--text-xl);
  font-weight: var(--font-normal);
}
@media screen and (min-width: 65em) {
  .k-headline[data-size="large"] {
    --size: var(--text-2xl);
  }
}
.k-headline[data-size="huge"] {
  --size: var(--text-2xl);
  line-height: 1.15em;
}
@media screen and (min-width: 65em) {
  .k-headline[data-size="huge"] {
    --size: var(--text-3xl);
  }
}
.k-headline[data-theme] {
  color: var(--theme);
}
.k-headline abbr {
  color: var(--color-gray-500);
  padding-inline-start: 0.25rem;
  text-decoration: none;
}
</style>
