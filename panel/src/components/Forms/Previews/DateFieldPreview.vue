<script>
import TextFieldPreview from "./TextFieldPreview.vue";

export default {
  inheritAttrs: false,
  extends: TextFieldPreview,
  props: {
    value: String
  },
  class: "k-date-field-preview",
  computed: {
    text() {
      if (typeof this.value !== "string") {
        return "";
      }

      const dt = this.$library.dayjs(this.value);

      if (!dt) {
        return "";
      }

      let format = this.field.display;

      if (this.field.time?.display) {
        format += " " + this.field.time.display;
      }

      return dt.format(format);
    }
  }
};
</script>
