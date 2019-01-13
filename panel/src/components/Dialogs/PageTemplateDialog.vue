<template>
  <k-dialog
    ref="dialog"
    :button="$t('change')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
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
      blueprints: [],
      page: {
        id: null,
        template: null
      }
    };
  },
  computed: {
    fields() {
      return {
        template: {
          label: this.$t("template"),
          type: "select",
          required: true,
          empty: false,
          options: this.page.blueprints,
          icon: "template"
        }
      };
    }
  },
  methods: {
    open(id) {
      this.$api.pages
        .get(id, { select: ["id", "template", "blueprints"] })
        .then(page => {
          if (page.blueprints.length <= 1) {
            return this.$store.dispatch("notification/error", {
              message: this.$t("error.page.changeTemplate.invalid", {
                slug: page.id
              })
            });
          }

          this.page = page;
          this.page.blueprints = this.page.blueprints.map(blueprint => ({
            text: blueprint.title,
            value: blueprint.name
          }));
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$events.$emit("keydown.cmd.s");
      this.$api.pages
        .template(this.page.id, this.page.template)
        .then(() => {
          this.success({
            message: ":)",
            event: "page.changeTemplate"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
