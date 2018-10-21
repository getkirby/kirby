<template>
  <k-dialog
    ref="dialog"
    :button="$t('delete')"
    theme="negative"
    icon="trash"
    size="medium"
    @submit="submit"
  >
    <template v-if="page.hasChildren || page.hasDrafts">
      <k-text v-html="$t('page.delete.confirm', { title: page.title })" />
      <div class="k-page-remove-warning">
        <k-box v-html="$t('page.delete.confirm.subpages')" theme="negative" />
      </div>
      <k-form v-if="hasSubpages" :fields="fields" v-model="model" @submit="submit" />
    </template>
    <template v-else>
      <k-text @keydown.enter="submit" v-html="$t('page.delete.confirm', { title: page.title })" />
    </template>
  </k-dialog>
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
      model: {
        check: null
      }
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
    open(id) {
      this.$api.pages.get(id, {select: "id, title, hasChildren, hasDrafts, parent"})
        .then(page => {
          this.page = page;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    submit() {

      if (this.hasSubpages && this.model.check !== this.page.title) {
        this.$refs.dialog.error(this.$t("error.page.delete.confirm"));
        return;
      }

      this.$api.pages
        .delete(this.page.id, { force: true })
        .then(() => {
          // remove data from cache
          this.$cache.remove(this.$route.path);

          const payload = {
            message: this.$t("page.deleted"),
            event: "page.delete"
          };

          // if in PageView, redirect
          if (
            this.$route.params.path &&
            this.page.id === this.$route.params.path.replace(/\+/g, "/")
          ) {
            if (this.page.parent) {
              payload.route = "/pages/" + this.page.parent.id;
            } else {
              payload.route = "/pages";
            }
          }

          this.success(payload);
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>

<style lang="scss">
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
