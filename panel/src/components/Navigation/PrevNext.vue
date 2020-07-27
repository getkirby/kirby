<template>
  <k-button-group class="k-prev-next">
    <k-button v-bind="prevSettings" icon="angle-left" />
    <k-button v-bind="nextSettings" icon="angle-right" />
  </k-button-group>
</template>

<script>
export default {
  props: {
    next: [Boolean, Object],
    prev: [Boolean, Object]
  },
  computed: {
    nextSettings() {
      return this.next || {
        disabled: true,
        link: "#",
      };
    },
    prevSettings() {
      return this.prev || {
        disabled: true,
        link: "#",
      };
    }
  }
};
</script>

<style lang="scss">
.k-prev-next {
  direction: ltr;
}
</style>
