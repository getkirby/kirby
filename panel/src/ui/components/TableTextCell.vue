<template>
  <p
    :class="'text-' + align"
    class="k-table-cell-value"
  >
    <template v-if="cellValue">
      {{ column.before }}
      {{ cellValue }}
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
      align() {
        return this.column.align || "left";
      },
      cellValue() {
        if (this.value === undefined) {
          return "";
        }

        if (Array.isArray(this.value)) {
          return this.value.toString();
        }

        if (typeof this.value === "object" && this.value !== null) {
          return "[ … ]";
        }

        return this.value.toString();
      }
    }
  }
</script>

