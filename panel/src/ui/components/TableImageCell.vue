<template>
  <div
    v-if="value"
    class="k-table-image-cell-figure px-3"
  >
    <k-image
      :back="column.back || 'black'"
      :cover="column.cover"
      :src="value"
      class="rounded-sm shadow-md overflow-hidden"
      style="width: 1.75rem"
    />
  </div>
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
  }
</script>

