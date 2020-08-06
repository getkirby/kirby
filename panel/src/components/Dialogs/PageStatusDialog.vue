<template>
  <k-form-dialog
    ref="dialog"
    v-model="form"
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
      page: {
        id: null
      },
      isBlocked: false,
      isIncomplete: false,
      form: {
        status: null,
        position: null
      },
      states: {}
    };
  },
  computed: {
    fields() {

      let fields = {
        status: {
          name: "status",
          label: this.$t("page.changeStatus.select"),
          type: "radio",
          required: true,
          options: Object.keys(this.states).map(key => {
            return {
              value: key,
              text: this.states[key].label,
              info: this.states[key].text
            };
          })
        }
      };

      if (
        this.form.status === "listed" &&
        this.page.blueprint.num === "default"
      ) {
        fields.position = {
          name: "position",
          label: this.$t("page.changeStatus.position"),
          type: "select",
          empty: false,
          options: this.sortingOptions()
        };
      }

      return fields;
    }
  },
  methods: {
    sortingOptions() {
      let options = [];
      let index = 0;

      this.page.siblings.forEach(sibling => {
        if (sibling.id === this.page.id || sibling.num < 1) {
          return false;
        }

        index++;

        options.push({
          value: index,
          text: index
        });

        options.push({
          value: sibling.id,
          text: sibling.title,
          disabled: true
        });
      });

      options.push({
        value: index + 1,
        text: index + 1
      });

      return options;
    },
    async open(id) {
      try {
        const page = await this.$api.pages.get(id, {
          select: ["id", "status", "num", "errors", "blueprint"]
        });

        if (page.blueprint.options.changeStatus === false) {
          return this.$store.dispatch("notification/error", {
            message: this.$t("error.page.changeStatus.permission")
          });
        }

        if (page.status === "draft" && Object.keys(page.errors).length > 0) {
          return this.$store.dispatch("notification/error", {
            message: this.$t("error.page.changeStatus.incomplete"),
            details: page.errors
          });
        }

        if (page.blueprint.num === "default") {
          try {
            const response = await this.$api.pages.get(id, {
              select: ["siblings"]
            });
            this.setup({
              ...page,
              siblings: response.siblings
            });
          } catch (error) {
            this.$store.dispatch('notification/error', error);
          }
        } else {
          this.setup({
            ...page,
            siblings: []
          });
        }

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    setup(page) {
      this.page          = page;
      this.form.position = page.num || (page.siblings.length + 1);
      this.form.status   = page.status;
      this.states        = page.blueprint.status;

      this.$refs.dialog.open();
    },
    async submit() {
      try {
        await this.$api.pages.changeStatus(
          this.page.id,
          this.form.status,
          this.form.position || 1
        );
        this.success({
          message: ":)",
          event: "page.changeStatus"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
