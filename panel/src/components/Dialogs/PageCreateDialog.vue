<template>
  <k-dialog
    ref="dialog"
    :button="$t('page.draft.create')"
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
          label: this.$t("title"),
          type: "text",
          required: true,
          icon: "title"
        },
        slug: {
          label: this.$t("slug"),
          type: "text",
          required: true,
          counter: false,
          icon: "url"
        },
        template: {
          name: "template",
          label: this.$t("template"),
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
    open(parent, blueprintApi, section) {
      this.parent  = parent;
      this.section = section;

      this.$api
        .get(blueprintApi, {section: section})
        .then(response => {
          this.templates = response.map(blueprint => {
            return {
              value: blueprint.name,
              text: blueprint.title
            };
          });

          if (this.templates[0]) {
            this.page.template = this.templates[0].value;
          }

          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });

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
        .post(this.parent + "/children", data)
        .then(page => {
          this.success({
            route: this.$api.pages.link(page.id),
            message: ":)",
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
