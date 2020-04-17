<template>
  <p class="k-table-cell-value">
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
