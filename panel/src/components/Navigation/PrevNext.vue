<template>
  <k-button-group
    :buttons="[
      { ...button(prev), icon: 'angle-left' },
      { ...button(next), icon: 'angle-right' }
    ]"
    class="k-prev-next"
  />
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
          link: "#"
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
