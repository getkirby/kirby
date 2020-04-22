<template>
  <p class="k-table-cell-value">
    <template v-if="link">
      {{ column.before }}
      <k-link
        :to="link"
        class="k-table-cell-link truncate underline"
        target="_blank"
        @click.native.stop
      >
        {{ value }}
      </k-link>
      {{ column.after }}
    </template>
  </p>
</template>

<script>
export default {
  props: {
    column: {
      type: Object,
      default() {
        return {};
      }
    },
    value: String,
  },
  computed: {
    link() {
      return this.value;
    }
  }
}
</script>

<style lang="scss">
.k-table-cell-link {
  color: $color-focus;
  transition: color .3s;
}
.k-table-cell-link:hover {
  color: $color-black;
}
</style>
