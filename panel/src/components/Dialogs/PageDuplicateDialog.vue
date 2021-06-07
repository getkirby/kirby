<template>
  <k-form-dialog
    ref="dialog"
    v-model="page"
    :fields="fields"
    :submit-button="$t('duplicate')"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      page: {
        children: false,
        files: false,
        hasChildren: false,
        hasDrafts: false,
        hasFiles: false,
        id: null,
        slug: ''
      }
    };
  },
  computed: {
    fields() {
      const hasChildren = this.page.hasChildren || this.page.hasDrafts;
      const hasFiles    = this.page.hasFiles;

      let fields = {
        slug: {
          label: this.$t("slug"),
          type: "text",
          required: true,
          counter: false,
          spellcheck: false,
          icon: "url",
        }
      };

      if (hasFiles) {
        fields.files = {
          label: this.$t("page.duplicate.files"),
          type: "toggle",
          required: true,
          width: hasChildren ? "1/2" : null
        };
      }

      if (hasChildren) {
        fields.children = {
          label: this.$t("page.duplicate.pages"),
          type: "toggle",
          required: true,
          width: hasFiles ? "1/2" : null
        };
      }

      return fields;
    }
  },
  watch: {
    "page.slug"(value) {
      this.page.slug = this.$helper.slug(value, [this.$system.slugs, this.$system.ascii]);
    }
  },
  methods: {
    async open(id) {
      try {
        const page = await this.$api.pages.get(id, {
          language: "@default",
          select: "id,slug,hasChildren,hasDrafts,hasFiles,title"
        });

        this.page.id          = page.id;
        this.page.slug        = page.slug + "-" + this.$helper.slug(this.$t("page.duplicate.appendix"));
        this.page.hasChildren = page.hasChildren;
        this.page.hasDrafts   = page.hasDrafts;
        this.page.hasFiles    = page.hasFiles;
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async submit() {
      try {
        const page = await this.$api.pages.duplicate(
          this.page.id,
          this.page.slug,
          {
            children: this.page.children,
            files:    this.page.files,
          }
        );

        this.success({
          route: this.$api.pages.link(page.id),
          message: ":)",
          event: "page.duplicate"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
