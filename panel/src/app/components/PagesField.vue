
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
    items: {
      type: Function,
      async default(ids) {
        const params = { ids: JSON.stringify(this.selected) };
        // TODO: actual API endpoint
        return this.$api.get("field/pages/items", params);
      },
    },
    type: {
      type: String,
      default: "pages"
    }
  }
}
</script>
