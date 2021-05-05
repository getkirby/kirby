<template>
  <k-remove-dialog
    ref="dialog"
    :size="hasSubpages ? 'medium' : 'small'"
    @submit="submit"
    @close="reset"
  >
    <template v-if="page.hasChildren || page.hasDrafts">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-html="$t('page.delete.confirm', { title: page.title })" />
      <div class="k-page-remove-warning">
        <!-- eslint-disable-next-line vue/no-v-html -->
        <k-box theme="negative" v-html="$t('page.delete.confirm.subpages')" />
      </div>
      <k-form
        v-if="hasSubpages"
        v-model="model"
        :fields="fields"
        @submit="submit"
      />
    </template>
    <template v-else>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text @keydown.enter="submit" v-html="$t('page.delete.confirm', { title: page.title })" />
    </template>
  </k-remove-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      page: {
        title: null,
        hasChildren: false,
        hasDrafts: false
      },
      model: this.emptyForm()
    };
  },
  computed: {
    hasSubpages() {
      return this.page.hasChildren || this.page.hasDrafts;
    },
    fields() {
      return {
        check: {
          label: this.$t("page.delete.confirm.title"),
          type: "text",
          counter: false
        }
      };
    }
  },
  methods: {
    emptyForm() {
      return {
        check: null
      };
    },
    async open(id) {
      try {
        this.page = await this.$api.pages.get(id, {
          select: "id, title, hasChildren, hasDrafts, parent"
        });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async submit() {

      if (this.hasSubpages && this.model.check !== this.page.title) {
        return this.$refs.dialog.error(this.$t("error.page.delete.confirm"));
      }

      try {
        await this.$api.pages.delete(this.page.id, { force: true });

        // remove data from cache
        this.$store.dispatch("content/remove", "pages/" + this.page.id);

        const payload = {
          message: ":)",
          event: "page.delete"
        };

        // if in PageView, redirect
        if (
          this.$route.params.path &&
          this.page.id === this.$route.params.path.replace(/\+/g, "/")
        ) {
          if (this.page.parent) {
            payload.route = this.$api.pages.link(this.page.parent.id);
          } else {
            payload.route = "/pages";
          }
        }

        this.success(payload);

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    },
    reset() {
      this.model = this.emptyForm();
    }
  }
};
</script>

<style>
.k-page-remove-warning {
  margin: 1.5rem 0;
}
.k-page-remove-warning .k-box {
  font-size: 1rem;
  line-height: 1.5em;
  padding-top: .75rem;
  padding-bottom: .75rem;
}
</style>
