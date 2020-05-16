<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {
      this.id = id;
      this.values = await this.$api.pages.get(id, {
        language: "@default",
        select: [
          "hasChildren",
          "hasDrafts",
          "hasFiles",
          "id",
          "slug",
          "title"
        ]
      });

      // add the appendix to the slug to make it more unique
      // and indicate that it needs to be changed
      this.values.slug += "-" + this.$helper.slug(this.$t("page.duplicate.appendix"));

      const hasChildren = this.values.hasChildren || this.values.hasDrafts;
      const hasFiles    = this.values.hasFiles;

      this.fields = {
        slug: {
          counter: false,
          icon: "url",
          label: this.$t("slug"),
          required: true,
          slug: true,
          spellcheck: false,
          type: "text",
        }
      };

      if (hasFiles) {
        this.fields.files = {
          label: this.$t("page.duplicate.files"),
          type: "toggle",
          required: true,
          width: hasChildren ? "1/2" : null
        };
      }

      if (hasChildren) {
        this.fields.children = {
          label: this.$t("page.duplicate.pages"),
          type: "toggle",
          required: true,
          width: hasFiles ? "1/2" : null
        };
      }

    },
    async submit() {
      // duplicate the current page with new slug
      const page = await this.$model.pages.duplicate(
        this.id,
        this.values.slug,
        {
          children: this.values.children,
          files:    this.values.files
        }
      );

      // route to new page
      const path = this.link(page.id);
      this.$router.push(path);

      return page;
    }
  }
}
</script>
