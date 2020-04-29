<script>
import TableTextCell from "./TableTextCell.vue";

export default {
  extends: TableTextCell,
  props: {
    value: [Number, String]
  },
  computed: {
    align() {
      return this.column.align || "right";
    },
    cellValue() {
      if (!this.value && this.value !== 0) {
        return null;
      }

      let number = Number(this.value || 0);

      if (this.column.precision || this.column.precision === 0) {
        return number.toFixed(this.column.precision);
      }

      if (number === 0) {
        return number.toFixed(0);
      }

      return number;
    }
  }
}
</script>
