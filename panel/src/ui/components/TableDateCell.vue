<script>
import TableTextCell from "./TableTextCell.vue";

export default {
  extends: TableTextCell,
  computed: {
    align() {
      return this.column.align || "right";
    },
    cellValue() {
      if (!this.value) {
        return "";
      }

      const date = this.$library.dayjs(this.value);

      if (date.isValid() === false) {
        return "";
      }

      const showDate = this.column.date !== false;
      const showTime = this.column.time === true || typeof this.column.time === "string";

      const dateFormat = typeof this.column.date === "string" ? this.column.date : "YYYY-MM-DD";
      const timeFormat = typeof this.column.time === "string" ? this.column.time : "HH:mm";

      let value = [];

      if (showDate !== false) {
        value.push(date.format(dateFormat));
      }

      if (showTime !== false) {
        value.push(date.format(timeFormat));
      }

      return value.join(" - ");
    }
  }
}
</script>

