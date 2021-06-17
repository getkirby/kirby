<template>
  <section
    v-if="isLoading === false"
    :data-processing="isProcessing"
    class="k-pages-section"
  >
    <header class="k-section-header">
      <k-headline :link="options.link">
        {{ headline }} <abbr v-if="options.min" :title="$t('section.required')">*</abbr>
      </k-headline>

      <k-button-group v-if="add">
        <k-button icon="add" @click="create">
          {{ $t("add") }}
        </k-button>
      </k-button-group>
    </header>

    <template v-if="error">
      <k-box theme="negative">
        <k-text size="small">
          <strong>
            {{ $t("error.section.notLoaded", { name: name }) }}:
          </strong>
          {{ error }}
        </k-text>
      </k-box>
    </template>

    <template v-else>
      <k-collection
        v-if="data.length"
        :layout="options.layout"
        :help="help"
        :items="data"
        :pagination="pagination"
        :sortable="!isProcessing && options.sortable"
        :size="options.size"
        :data-invalid="isInvalid"
        @change="sort"
        @paginate="paginate"
        @action="action"
      />

      <template v-else>
        <k-empty
          :layout="options.layout"
          :data-invalid="isInvalid"
          icon="page"
          @click="create"
        >
          {{ options.empty || $t('pages.empty') }}
        </k-empty>
        <footer class="k-collection-footer">
          <!-- eslint-disable vue/no-v-html -->
          <k-text
            v-if="help"
            theme="help"
            class="k-collection-help"
            v-html="help"
          />
          <!-- eslint-enable vue/no-v-html -->
        </footer>
      </template>

      <k-page-rename-dialog ref="rename" @success="update" />
    </template>
  </section>
</template>

<script>
import CollectionSectionMixin from "@/mixins/section/collection.js";

export default {
  mixins: [CollectionSectionMixin],
  computed: {
    add() {
      return this.options.add && this.$permissions.pages.create;
    }
  },
  created() {
    this.load();
    this.$events.$on("page.changeStatus", this.reload);
  },
  destroyed() {
    this.$events.$off("page.changeStatus", this.reload);
  },
  methods: {
    create() {
      if (this.add) {
        this.$dialog('pages/create', {
          parent: this.parent,
          section: this.name
        });
      }
    },
    action(action, page) {

      switch (action) {
        case "preview": {
          let preview = window.open("", "_blank");
          preview.document.write = "...";

          this.$api.pages
            .preview(page.id)
            .then(url => {
              preview.location.href = url;
            })
            .catch(error => {
              this.$store.dispatch("notification/error", error);
            });

          break;
        }
        case "remove": {
          // TODO: this will never be called
          // with the new options menu. Should
          // probably be possible to filter
          // the options menu from the outside instead.
          if (this.data.length <= this.options.min) {
            const number = this.options.min > 1 ? "plural" : "singular";
            this.$store.dispatch("notification/error", {
              message: this.$t("error.section.pages.min." + number, {
                section: this.options.headline || this.name,
                min: this.options.min
              })
            });
            break;
          }

          this.$dialog(`${page.link}/delete`);
          break;
        }
        default: {
          throw new Error("Invalid action");
        }
      }

    },
    items(data) {
      return data.map(page => {
        const isEnabled = page.permissions.changeStatus !== false;

        page.flag = {
          status: page.status,
          tooltip: this.$t("page.status"),
          disabled: !isEnabled,
          click: () => {
            this.$dialog(this.$api.pages.url(page.id) + "/changeStatus");
          }
        };

        page.sortable = page.permissions.sort && this.options.sortable;
        page.column   = this.column;
        page.options  = async ready => {
          try {
            const options = await this.$api.pages.options(
              page.id,
              "list",
              page.sortable
            );
            ready(options);

          } catch (error) {
            this.$store.dispatch("notification/error", error);
          }
        };

        return page;
      });
    },
    async sort(event) {
      let type = null;

      if (event.added) {
        type = "added";
      }

      if (event.moved) {
        type = "moved";
      }

      if (type) {
        this.isProcessing = true;

        const element = event[type].element;
        const position = event[type].newIndex + 1 + this.pagination.offset;

        try {
          await this.$api.pages.status(element.id, "listed", position);
          this.$store.dispatch("notification/success", ":)");

        } catch (error) {
          this.$store.dispatch("notification/error", {
            message: error.message,
            details: error.details
          });

          await this.reload();

        } finally {
          this.isProcessing = false;
        }
      }
    },
    update() {
      this.reload();
      this.$events.$emit("model.update");
    }
  }
};
</script>

<style>
.k-pages-section[data-processing] {
  pointer-events: none;
}
</style>
