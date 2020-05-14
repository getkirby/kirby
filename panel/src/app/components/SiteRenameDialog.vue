<script>
import PageRenameDialog from "./PageRenameDialog.vue";

export default {
  extends: PageRenameDialog,
  methods: {
    async load() {
      this.values = await this.$api.site.get({
        select: ["title"]
      });
    },
    async submit() {
      return await this.$api.site.changeTitle(this.values.title);
    },
    async validate() {
      if (this.values.title.length === 0) {
        throw this.$t("error.site.changeTitle.empty");
      }
    }
  }
}
</script>
