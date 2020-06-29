<template>
  <k-button-group class="k-prev-next">
    <k-button
      v-bind="prev"
      :disabled="!prev"
      icon="angle-left"
    />
    <k-button
      v-bind="next"
      :disabled="!next"
      icon="angle-right"
    />
  </k-button-group>
</template>

<script>
export default {
  props: {
    prev: {
      type: [Object, Boolean],
      default() {
        return {
          disabled: true,
          link: "#",
        }
      }
    },
    next: {
      type: [Object, Boolean],
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
