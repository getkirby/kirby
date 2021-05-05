<template>
  <ul class="k-list">
    <!-- @slot If you need more control over each item, you can inject items manually -->
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
/**
 * @example <k-list
 *   :items="[
 *     {...},
 *     {...},
 *     {...},
 *   ]"
 * />
 */
export default {
  props: {
    /**
     * Check out the `<k-list-item>` component documentation for all available options for each item.
     */
    items: Array
  }
};
</script>

<style>
.k-list .k-list-item:not(:last-child) {
  margin-bottom: 2px;
}
</style>
