<template>
  <component
    :is="tag"
    :data-theme="theme"
    :data-size="size"
    class="k-headline"
    v-on="$listeners"
  >
    <k-link v-if="link" :to="link"><slot /></k-link>
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
  font-size: $font-size-medium;
  font-weight: $font-weight-bold;
  line-height: 1.5em;
}
.k-headline[data-size="small"] {
  font-size: $font-size-small;
}
.k-headline[data-size="large"] {
  font-size: $font-size-large;
  font-weight: $font-weight-normal;

  @media screen and (min-width: $breakpoint-medium) {
    font-size: $font-size-huge;
  }
}
.k-headline[data-size="huge"] {
  font-size: $font-size-huge;
  line-height: 1.15em;

  @media screen and (min-width: $breakpoint-medium) {
    font-size: $font-size-monster;
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
