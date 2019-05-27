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
import slug from "@/helpers/slug.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      notification: null,
      page: {
        copyFiles: false,
        id: null,
        slug: '',
      }
    };
  },
  computed: {
    fields() {
      return {
        slug: {
          label: this.$t("slug"),
          type: "text",
          required: true,
          counter: false,
          spellcheck: false,
          icon: "url"
        },
        copyFiles: {
          label: this.$t("page.duplicate.files"),
          type: "toggle",
          required: true,
        }
      };
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
      this.page.slug = slug(value, [this.slugs, this.system.ascii]);
    }
  },
  methods: {
    open(id) {
      this.$api.pages
        .get(id, {language: "@default"})
        .then(page => {
          this.page.id   = page.id;
          this.page.slug = page.slug + "-" + slug(this.$t("copy"));
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    submit() {
      this.$api.pages
        .duplicate(this.page.id, this.page.slug, this.page.copyFiles)
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
