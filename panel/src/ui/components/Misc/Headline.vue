<template>
  <component
    :is="tag"
    :data-theme="theme"
    :data-size="size"
    class="kirby-headline"
    v-on="$listeners"
  >
    <kirby-link v-if="link" :to="link"><slot /></kirby-link>
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
.kirby-headline {
  font-size: $font-size-medium;
  font-weight: $font-weight-bold;
  line-height: 1.5em;
}
.kirby-headline[data-size="small"] {
  font-size: $font-size-small;
}
.kirby-headline[data-size="large"] {
  font-size: $font-size-large;
  font-weight: $font-weight-normal;

  @media screen and (min-width: $breakpoint-medium) {
    font-size: $font-size-huge;
  }
}
.kirby-headline[data-size="huge"] {
  font-size: $font-size-huge;
  line-height: 1.15em;

  @media screen and (min-width: $breakpoint-medium) {
    font-size: $font-size-monster;
  }
}
.kirby-headline[data-theme="negative"] {
  color: $color-negative;
}
.kirby-headline[data-theme="positive"] {
  color: $color-positive;
}

.kirby-headline abbr {
  color: $color-light-grey;
  padding-left: .25rem;
  text-decoration: none;
}
</style>
