<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('create')"
    :notification="notification"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="page"
      @submit="submit"
    />
  </kirby-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import slug from "@/ui/helpers/slug.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      notification: null,
      parent: null,
      section: null,
      templates: [],
      page: {
        title: '',
        slug: '',
        template: null
      }
    };
  },
  computed: {
    fields() {
      return {
        title: {
          label: this.$t("page.title"),
          type: "text",
          required: true,
          icon: "title"
        },
        slug: {
          label: this.$t("page.slug"),
          type: "text",
          required: true,
          counter: false,
          icon: "url"
        },
        template: {
          name: "template",
          label: this.$t("page.template"),
          type: "select",
          disabled: this.templates.length === 1,
          required: true,
          icon: "code",
          empty: false,
          options: this.templates
        }
      };
    }
  },
  watch: {
    "page.title"(title) {
      this.page.slug = slug(title);
    }
  },
  methods: {
    open(parent, section, templates) {
      this.parent = parent;
      this.section = section;
      this.templates = templates;
      this.page.template = templates[0].value;
      this.$refs.dialog.open();
    },
    submit() {

      if (this.page.title.length === 0) {
        this.$refs.dialog.error('Please enter a title');
        return false;
      }

      const data = {
        template: this.page.template,
        slug: this.page.slug,
        content: {
          title: this.page.title
        }
      };

      this.$api
        .post(this.parent + "/" + this.section, data)
        .then(page => {
          this.success({
            route: this.$api.pages.link(page.id),
            message: this.$t("page.created"),
            event: "page.create"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
