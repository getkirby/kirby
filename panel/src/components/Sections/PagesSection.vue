<template>
  <section
    v-if="isLoading === false"
    :data-processing="isProcessing"
    class="k-pages-section"
  >
    <header class="k-section-header">
      <k-headline :link="options.link">
        {{ headline }}
        <abbr v-if="options.min" :title="$t('section.required')">*</abbr>
      </k-headline>

      <k-button-group
        v-if="add"
        :buttons="[{ text: $t('add'), icon: 'add', click: create }]"
      />
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
      />

      <template v-else>
        <k-empty
          :layout="options.layout"
          :data-invalid="isInvalid"
          icon="page"
          v-on="add ? { click: create } : {}"
        >
          {{ options.empty || $t("pages.empty") }}
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
    this.$events.$on("page.sort", this.reload);
  },
  destroyed() {
    this.$events.$off("page.changeStatus", this.reload);
    this.$events.$off("page.sort", this.reload);
  },
  methods: {
    create() {
      if (this.add) {
        this.$dialog("pages/create", {
          query: {
            parent: this.options.link || this.parent,
            view: this.parent,
            section: this.name
          }
        });
      }
    },
    items(data) {
      return data.map((page) => {
        const isEnabled = page.permissions.changeStatus !== false;

        page.flag = {
          status: page.status,
          tooltip: this.$t("page.status"),
          disabled: !isEnabled,
          click: () => {
            this.$dialog(page.link + "/changeStatus");
          }
        };

        page.sortable = page.permissions.sort && this.options.sortable;
        page.deletable = data.length > this.options.min;
        page.column = this.column;
        page.options = this.$dropdown(page.link, {
          query: {
            view: "list",
            delete: page.deletable,
            sort: page.sortable
          }
        });

        // add data-attributes info for item
        page.data = {
          "data-id": page.id,
          "data-status": page.status,
          "data-template": page.template
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
          this.$events.$emit("page.sort", element);
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
.k-pages-section[data-processing="true"] {
  pointer-events: none;
}
</style>
