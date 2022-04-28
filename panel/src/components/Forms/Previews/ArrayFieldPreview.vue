<script>
import BubblesFieldPreview from "./BubblesFieldPreview.vue";

export default {
  inheritAttrs: false,
  extends: BubblesFieldPreview,
  class: "k-array-field-preview",
  computed: {
    bubbles() {
      return [
        {
          text:
            this.value.length === 1 ? `1 entry` : `${this.value.length} entries`
        }
      ];
    }
  }
};
</script>
