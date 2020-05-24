<template>
  <k-dialog
    ref="dialog"
    :loading="isLoading"
    :cancel-button="cancelButton"
    :submit-button="submitButton"
    @submit="onSubmit"
  >
    <template v-if="hasChildren">
      <k-text
        v-html="text"
        class="mb-3"
      />
      <k-text
        v-html="$t('page.delete.confirm.subpages')"
        class="k-page-remove-warning mb-8 bg-orange-light py-2 px-3 text-sm rounded-sm"
      />
      <k-form
        v-model="values"
        :fields="fields"
        @submit="onSubmit"
      />
    </template>
    <template v-else>
      <k-text
        v-html="text"
        @keydown.enter="submit"
      />
    </template>
  </k-dialog>
</template>
<script>
import AsyncDialog from "@/ui/components/AsyncDialog.vue";

export default {
  extends: AsyncDialog,
  data() {
    return {
      fields: {},
      hasChildren: false,
      id: null,
      parent: null,
      title: null,
      values: {}
    };
  },
  methods: {
    async load(id) {
      const page = await this.$api.pages.get(id, {
        select: [
          "title",
          "parent",
          "hasChildren",
          "hasDrafts"
        ]
      });

      // keep the id to delete the page later
      this.id = id;

      // keep the parent to redirect router later
      this.parent = page.parent;

      // check if there are any subpages or drafts
      // in this case, additional confirmation is needed
      this.hasChildren = page.hasChildren || page.hasDrafts;

      // keep the page title to compare it with
      // the result of the confirmation check
      this.title = page.title;

      // confirmation text
      this.text = this.$t("page.delete.confirm", {
        title: page.title
      });

      // values for the confirmation form
      this.values = {
        check: ""
      };

      // fields for the extra confirmation step
      this.fields = {
        check: {
          counter: false,
          label: this.$t("page.delete.confirm.title"),
          required: true,
          trim: true,
          type: "text",
        }
      };

      this.submitButton = {
        color: "red",
        icon: "trash",
        text: this.$t("delete"),
      };
    },
    async submit() {
      await this.$model.pages.delete(this.id, { force: true });
    },
    async validate() {
      if (this.hasChildren === false) {
        return true;
      }

      if (this.values.check !== this.title) {
        throw this.$t("error.page.delete.confirm");
      }
    }
  }
}
</script>
