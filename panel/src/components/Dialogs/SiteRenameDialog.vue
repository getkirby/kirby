
<script>
import PageRename from "./PageRenameDialog.vue";

export default {
  extends: PageRename,
  methods: {
    open() {
      this.$api.site.get({ select: ["title"] }).then(site => {
        this.page = site;
        this.$refs.dialog.open();
      });
    },
    submit() {
      this.$api.site
        .title(this.page.title)
        .then(() => {
          this.success({
            message: this.$t("site.renamed"),
            event: "site.changeTitle"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
