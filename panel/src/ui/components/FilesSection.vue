<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
  computed: {
    defaultEmpty() {
      return {
        icon: "file",
        text: this.$t("files.empty")
      };
    },
    options() {
      if (this.add === false) {
        return [];
      }

      return [
        {
          icon: "upload",
          option: "add",
          text: "Add",
        },
      ];
    },
    type() {
      return "files";
    }
  }
};
</script>
