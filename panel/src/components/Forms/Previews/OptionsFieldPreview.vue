<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
  inheritAttrs: false,
  extends: BubblesFieldPreview,
  class: "k-options-field-preview",
  props: {
    value: [Array, String]
  },
  computed: {
    bubbles() {
      let options = this.value;

      if (typeof options === "string") {
        options = options.split(",");
      }

      return options.map((item) => {
        if (typeof item === "string") {
          item = {
            value: item,
            text: item
          };
        }

        for (const option of this.field.options || []) {
          if (option.value === item.value) {
            item.text = option.text;
          }
        }

        return {
          back: "light",
          color: "black",
          ...item
        };
      });
    }
  }
};
</script>
