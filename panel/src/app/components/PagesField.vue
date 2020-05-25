
<script>
import ModelsField from "@/app/components/ModelsField.vue";

export default {
  extends: ModelsField,
  props: {
    empty: {
      type: [String, Object],
      default() {
        return {
          icon: "page",
          text: this.$t("field.pages.empty")
        };
      }
    },
    type: {
      type: String,
      default: "pages"
    }
  }
}
</script>
