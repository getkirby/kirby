
<script>
import PageStatusDialog from "./PageStatusDialog.vue";

export default {
  extends: PageStatusDialog,
  computed: {
    fields() {
      return {
        position: {
          name: "position",
          label: this.$t("page.changeStatus.position"),
          type: "select",
          empty: false,
          options: this.sortingOptions
        }
      };
    }
  },
  methods: {
    async open(id) {
      try {
        const page = await this.$api.pages.get(id, {
          select: ["id", "status", "num", "errors", "blueprint", "siblings"]
        });

        this.setup(page);

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.pages.status(
          this.page.id,
          "listed",
          this.form.position || 1
        );

        this.success({
          message: ":)",
          event: "page.sort"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
