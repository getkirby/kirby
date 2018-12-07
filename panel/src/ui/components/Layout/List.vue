<template>
  <ul class="k-list">
    <slot>
      <k-list-item
        v-for="(item, index) in items"
        :key="index"
        v-bind="item"
        v-on="$listeners"
      />
    </slot>
  </ul>
</template>

<script>
export default {
  props: {
    items: Array
  }
};
</script>

<style lang="scss">
.k-list .k-list-item:not(:last-child) {
  margin-bottom: 2px;
}
</style>
