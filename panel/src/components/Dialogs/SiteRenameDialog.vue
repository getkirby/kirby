
<script>
import PageRename from "./PageRenameDialog.vue";

export default {
  extends: PageRename,
  methods: {
    async open() {
      try {
        this.page = await this.$api.site.get({ select: ["title"] });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      // prevent empty title with just spaces
      this.page.title = this.page.title.trim();

      if (this.page.title.length === 0) {
        this.$refs.dialog.error(this.$t("error.site.changeTitle.empty"));
        return;
      }

      try {
        await this.$api.site.changeTitle(this.page.title);
        this.$store.dispatch("system/title", this.page.title);
        this.success({
          message: ":)",
          event: "site.changeTitle"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
