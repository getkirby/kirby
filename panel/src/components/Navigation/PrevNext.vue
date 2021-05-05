<template>
  <k-button-group class="k-prev-next">
    <k-button v-bind="button(prev)" icon="angle-left" />
    <k-button v-bind="button(next)" icon="angle-right" />
  </k-button-group>
</template>

<script>
export default {
  props: {
    prev: {
      type: [Boolean, Object],
      default: false
    },
    next: {
      type: [Boolean, Object],
      default: false
    }
  },
  methods: {
    button(config) {
      if (!config) {
        return {
          disabled: true,
          link: "#",
        };
      }

      return config;
    }
  }
};
</script>

<style>
.k-prev-next {
  direction: ltr;
}
</style>
