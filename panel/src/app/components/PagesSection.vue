
<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
  computed: {
    defaultEmpty() {
      return {
        icon: "page",
        text: this.$t("pages.empty")
      };
    },
    options() {
      if (this.add === false) {
        return [];
      }

      return [
        {
          icon: "add",
          option: "add",
          text: "Add",
        },
      ];
    },
    type() {
      return "pages";
    }
  },
};
</script>
