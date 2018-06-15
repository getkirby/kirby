<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('change')"
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
          label: this.$t("page.template"),
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
      this.$api.page
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
      this.$events.$emit("key.save");
      this.$api.page
        .template(this.page.id, this.page.template)
        .then(() => {
          this.success({
            message: this.$t("page.template.changed"),
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
