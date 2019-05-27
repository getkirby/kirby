<template>
  <section v-if="isLoading === false" class="k-pages-section">

    <header class="k-section-header">
      <k-headline :link="options.link">
        {{ headline }} <abbr v-if="options.min" title="This section is required">*</abbr>
      </k-headline>

      <k-button-group v-if="add">
        <k-button icon="add" @click="action(null, 'create')">{{ $t("add") }}</k-button>
      </k-button-group>
    </header>

    <template v-if="error">
      <k-box theme="negative">
        <k-text size="small">
          <strong>{{ $t("error.section.notLoaded", { name: name }) }}:</strong>
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
        :sortable="options.sortable"
        :size="options.size"
        @change="sort"
        @paginate="paginate"
        @action="action"
      />

      <template v-else>
        <k-empty
          :layout="options.layout"
          icon="page"
          @click="if (add) action(null, 'create')"
        >
          {{ options.empty || $t('pages.empty') }}
        </k-empty>
        <footer class="k-collection-footer">
          <k-text
            v-if="help"
            theme="help"
            class="k-collection-help"
            v-html="help"
          />
        </footer>
      </template>

      <k-page-create-dialog ref="create" />
      <k-page-duplicate-dialog ref="duplicate" />
      <k-page-rename-dialog ref="rename" @success="update" />
      <k-page-url-dialog ref="url" @success="update" />
      <k-page-status-dialog ref="status" @success="update" />
      <k-page-template-dialog ref="template" @success="update" />
      <k-page-remove-dialog ref="remove" @success="update" />

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
    action(page, action) {
      switch (action) {
        case "create": {
          this.$refs.create.open(
            this.options.link || this.parent,
            this.parent + "/children/blueprints",
            this.name
          );
          break;
        }
        case "duplicate": {
          this.$refs.duplicate.open(page.id);
          break;
        }
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
        case "rename": {
          this.$refs.rename.open(page.id);
          break;
        }
        case "url": {
          this.$refs.url.open(page.id);
          break;
        }
        case "status": {
          this.$refs.status.open(page.id);
          break;
        }
        case "template": {
          this.$refs.template.open(page.id);
          break;
        }
        case "remove": {
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

          this.$refs.remove.open(page.id);
          break;
        }
        default: {
          throw new Error("Invalid action");
        }
      }
    },
    items(data) {
      return data.map(page => {
        page.flag = {
          class: "k-status-flag k-status-flag-" + page.status,
          tooltip: this.$t("page.status"),
          icon:
            page.permissions.changeStatus === false ? "protected" : "circle",
          disabled: page.permissions.changeStatus === false,
          click: () => {
            this.action(page, "status");
          }
        };

        page.options = ready => {
          this.$api.pages
            .options(page.id, "list")
            .then(options => ready(options))
            .catch(error => {
              this.$store.dispatch("notification/error", error);
            });
        };

        page.sortable = page.permissions.sort && this.options.sortable;

        return page;
      });
    },
    sort(event) {
      let type = null;

      if (event.added) {
        type = "added";
      }

      if (event.moved) {
        type = "moved";
      }

      if (type) {
        const element = event[type].element;
        const position = event[type].newIndex + 1 + this.pagination.offset;

        this.$api.pages
          .status(element.id, "listed", position)
          .then(() => {
            this.$store.dispatch("notification/success", ":)");
          })
          .catch(response => {
            this.$store.dispatch("notification/error", {
              message: response.message,
              details: response.details
            });

            this.reload();
          });
      }
    },
    update() {
      this.reload();
      this.$events.$emit("model.update");
    }
  }
};
</script>
