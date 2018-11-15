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
}
</script>
