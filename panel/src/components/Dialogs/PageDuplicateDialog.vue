<template>
  <k-dialog
    ref="dialog"
    :button="$t('duplicate')"
    :notification="notification"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      :novalidate="true"
      v-model="page"
      @submit="submit"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      notification: null,
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
    },
    slugs() {
      return this.$store.state.languages.default ? this.$store.state.languages.default.rules : this.system.slugs;
    },
    system() {
      return this.$store.state.system.info;
    }
  },
  watch: {
    "page.slug"(value) {
      this.page.slug = this.$helper.slug(value, [this.slugs, this.system.ascii]);
    }
  },
  methods: {
    open(id) {
      this.$api.pages
        .get(id, {language: "@default", select: "id,slug,hasChildren,hasDrafts,hasFiles,title"})
        .then(page => {
          this.page.id          = page.id;
          this.page.slug        = page.slug + "-" + this.$helper.slug(this.$t("page.duplicate.appendix"));
          this.page.hasChildren = page.hasChildren;
          this.page.hasDrafts   = page.hasDrafts;
          this.page.hasFiles    = page.hasFiles;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    submit() {
      this.$api.pages
        .duplicate(this.page.id, this.page.slug, {
          children: this.page.children,
          files:    this.page.files,
        })
        .then(page => {
          this.success({
            route: this.$api.pages.link(page.id),
            message: ":)",
            event: "page.duplicate"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
