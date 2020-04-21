<template>
  <component
    :is="tag"
    :data-theme="theme"
    :data-size="size"
    class="k-headline"
    v-on="$listeners"
  >
    <k-link
      v-if="link"
      :to="link"
    >
      <slot />
    </k-link>
    <slot v-else />
  </component>
</template>

<script>

export default {
  props: {
    link: String,
    size: {
      type: String
    },
    tag: {
      type: String,
      default: "h2"
    },
    theme: {
      type: String
    }
  }
};
</script>

<style lang="scss">
.k-headline {
  font-size: $text-base;
  font-weight: $font-bold;
  line-height: 1.25em;
}
.k-headline[data-size="small"] {
  font-size: $text-sm;
}
.k-headline[data-size="large"] {
  font-size: $text-xl;
  font-weight: $font-normal;

  @media screen and (min-width: $breakpoint-medium) {
    font-size: $text-2xl;
  }
}
.k-headline[data-size="huge"] {
  font-size: $text-2xl;
  line-height: 1.15em;

  @media screen and (min-width: $breakpoint-medium) {
    font-size: $text-3xl;
  }
}
.k-headline[data-theme="negative"] {
  color: $color-negative;
}
.k-headline[data-theme="positive"] {
  color: $color-positive;
}

.k-headline abbr {
  color: $color-light-grey;
  padding-left: .25rem;
  text-decoration: none;
}
</style>
