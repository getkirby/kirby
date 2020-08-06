<template>
  <k-form-dialog
    ref="dialog"
    v-model="page"
    :fields="fields"
    :submit-button="$t('change')"
    @submit="submit"
  />
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
    async open(id) {
      try {
        const page = await this.$api.pages.get(id, {
          select: ["id", "template", "blueprints"]
        });

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

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      this.$events.$emit("keydown.cmd.s");

      try {
        await this.$api.pages.changeTemplate(this.page.id, this.page.template);
        this.success({
          message: ":)",
          event: "page.changeTemplate"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
