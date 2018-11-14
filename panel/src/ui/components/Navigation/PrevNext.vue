<template>
  <k-button-group class="k-prev-next">
    <k-button :disabled="!prev" v-bind="prev" icon="angle-left" />
    <k-button :disabled="!next" v-bind="next" icon="angle-right" />
  </k-button-group>
</template>

<script>
export default {
  props: {
    prev: Object,
    next: Object
  },
};
</script>

<style lang="scss">
.k-prev-next {
  direction: ltr;
}
</style>
