
<script>
import PageRename from "./PageRenameDialog.vue";

export default {
  extends: PageRename,
  methods: {
    open() {
      this.$api.site.get({ select: ["title"] })
        .then(site => {
          this.page = site;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.site
        .title(this.page.title)
        .then(() => {
          this.$store.dispatch("system/title", this.page.title);
          this.success({
            message: ":)",
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
