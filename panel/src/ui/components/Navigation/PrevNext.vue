<template>
  <k-button-group class="k-prev-next">
    <k-button v-bind="prev" icon="angle-left" />
    <k-button v-bind="next" icon="angle-right" />
  </k-button-group>
</template>

<script>
export default {
  props: {
    prev: {
      type: Object,
      default() {
        return {
          disabled: true,
          link: "#",
        }
      }
    },
    next: {
      type: Object,
      default() {
        return {
          disabled: true,
          link: "#"
        }
      }
    }
  },
};
</script>

<style lang="scss">
.k-prev-next {
  direction: ltr;
}
</style>
