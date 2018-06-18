<template>
  <section v-if="isLoading === false" :data-error="error" class="kirby-pages-section">

    <header class="kirby-section-header">
      <kirby-headline :link="link">
        {{ headline }} <abbr v-if="min" title="This section is required">*</abbr>
      </kirby-headline>
      <kirby-button-group v-if="add">
        <kirby-button icon="add" @click="action(null, 'create')">{{ $t("add") }}</kirby-button>
      </kirby-button-group>
    </header>




    <template v-if="issue">
      <kirby-box theme="negative">
        <kirby-text size="small">
          <strong>{{ $t("error.blueprint.section.notLoaded", { name: this.name }) }}:</strong>
          {{ issue }}
        </kirby-text>
      </kirby-box>
    </template>
    <template v-else>

      <kirby-collection
        :layout="layout"
        :items="data"
        :pagination="pagination"
        :sortable="true"
        :group="group"
        @change="sort"
        @paginate="paginate"
        @action="action"
      />

      <kirby-page-create-dialog ref="create" />
      <kirby-page-rename-dialog ref="rename" @success="fetch" />
      <kirby-page-url-dialog ref="url" @success="fetch" />
      <kirby-page-status-dialog ref="status" @success="fetch" />
      <kirby-page-template-dialog ref="template" @success="fetch" />
      <kirby-page-remove-dialog ref="remove" @success="fetch" />

    </template>

    <div v-if="error" class="kirby-pages-section-error">
      {{ error.message }}
    </div>

  </section>

</template>

<script>
import SectionMixin from "@/mixins/section.js";

export default {
  mixins: [SectionMixin],
  data() {
    return {
      add: false,
      blueprints: [],
      data: [],
      issue: false,
      error: false,
      group: null,
      headline: null,
      isLoading: true,
      min: null,
      max: null,
      layout: "list",
      status: null,
      page: 1,
      link: false
    };
  },
  created() {
    this.fetch();
    this.$events.$on("page.changeStatus", this.fetch);
  },
  destroyed() {
    this.$events.$off("page.changeStatus", this.fetch);
  },
  methods: {
    fetch() {
      this.$api
        .section(this.parent, this.name, { page: this.page })
        .then(response => {
          this.data = response.data.map(page => {
            page.options = ready => {
              this.$api.page
                .options(page.id, "list")
                .then(options => ready(options));
            };

            const icons = {
              draft: "draft",
              listed: "toggle-on",
              unlisted: "toggle-off"
            };

            page.flag = {
              label: null,
              class: "kirby-list-collection-toggle kirby-pages-section-flag",
              icon: icons[page.status],
              click: () => {
                this.action(page, "status");
              }
            };

            return page;
          });

          this.add = response.add;
          this.blueprints = response.blueprints;
          this.pagination = response.pagination;
          this.status = response.status;
          this.group = response.group;
          this.headline = response.headline;
          this.sortable = response.sortable;
          this.min = response.min;
          this.max = response.max;
          this.layout = response.layout || "list";
          this.link = response.link;
          this.error = response.errors[0];
          this.isLoading = false;
        })
        .catch(error => {
          this.isLoading = false;
          this.issue = error.message;
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

        this.$api
          .patch(this.parent + "/" + this.name + "/sort", {
            page: element.id,
            status: this.status,
            position: position
          })
          .then(() => {
            this.$store.dispatch(
              "notification/success",
              this.$t("page.sorted")
            );
            this.$events.$emit("page.changeStatus");
          })
          .catch(response => {
            this.$store.dispatch("notification/error", {
              message: response.message,
              details: response.details
            });
            this.$events.$emit("page.changeStatus");
          });
      }
    },
    action(page, action) {
      switch (action) {
        case "create":
          this.$refs.create.open(
            this.parent,
            this.name,
            this.blueprints.map(blueprint => {
              return {
                value: blueprint.name,
                text: blueprint.title
              };
            })
          );
          break;
        case "preview":
          window.open(page.url);
          break;
        case "rename":
          this.$refs.rename.open(page.id);
          break;
        case "url":
          this.$refs.url.open(page.id);
          break;
        case "status":
          this.$refs.status.open(page.id);
          break;
        case "template":
          this.$refs.template.open(page.id);
          break;
        case "remove":
          this.$refs.remove.open(page.id);
          break;
      }
    },
    paginate(pagination) {
      this.page = pagination.page;
      this.fetch();
    }
  }
};
</script>

<style lang="scss">
.kirby-pages-section-flag .kirby-icon {
  opacity: 0.25;
  transition: opacity 0.3s;
}
.kirby-pages-section-flag:focus .kirby-icon,
.kirby-pages-section-flag:hover .kirby-icon {
  opacity: 1;
}
</style>
