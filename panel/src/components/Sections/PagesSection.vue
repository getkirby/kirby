<template>
  <section v-if="isLoading === false" :data-error="error" class="k-pages-section">

    <header class="k-section-header">
      <k-headline :link="link">
        {{ headline }} <abbr v-if="min" title="This section is required">*</abbr>
      </k-headline>
      <k-button-group v-if="add">
        <k-button icon="add" @click="action(null, 'create')">{{ $t("add") }}</k-button>
      </k-button-group>
    </header>

    <template v-if="issue">
      <k-box theme="negative">
        <k-text size="small">
          <strong>{{ $t("error.blueprint.section.notLoaded", { name: name }) }}:</strong>
          {{ issue }}
        </k-text>
      </k-box>
    </template>
    <template v-else>

      <k-collection
        v-if="data.length"
        :layout="layout"
        :items="data"
        :pagination="pagination"
        :sortable="sortable"
        @change="sort"
        @paginate="paginate"
        @action="action"
      />
      <k-empty v-else icon="page" @click="if (add) action(null, 'create')">
        {{ $t('pages.empty') }}
      </k-empty>

      <k-page-create-dialog ref="create" />
      <k-page-rename-dialog ref="rename" @success="update" />
      <k-page-url-dialog ref="url" @success="update" />
      <k-page-status-dialog ref="status" @success="update" />
      <k-page-template-dialog ref="template" @success="update" />
      <k-page-remove-dialog ref="remove" @success="update" />

    </template>

    <div v-if="error" class="k-pages-section-error">
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
      data: [],
      issue: false,
      error: false,
      headline: null,
      isLoading: true,
      min: null,
      max: null,
      layout: "list",
      page: 1,
      link: false
    };
  },
  computed: {
    language() {
      return this.$store.state.languages.current;
    }
  },
  watch: {
    language() {
      this.fetch();
    }
  },
  created() {
    this.fetch();
    this.$events.$on("page.changeStatus", this.fetch);
  },
  destroyed() {
    this.$events.$off("page.changeStatus", this.fetch);
  },
  methods: {
    action(page, action) {
      switch (action) {
        case "create":
          this.$refs.create.open(
            this.link || this.parent,
            this.parent + '/children/blueprints',
            this.name
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
    fetch() {
      this.$api
        .get(this.parent + "/sections/" + this.name, { page: this.page })
        .then(response => {

          this.data = response.data.map(page => {
            page.options = ready => {
              this.$api.pages
                .options(page.id, "list")
                .then(options => ready(options))
                .catch(error => {
                  this.$store.dispatch("notification/error", error);
                });
            };

            page.sortable = page.permissions.sort && response.options.sortable;

            page.flag = {
              tooltip: page.statusLabel,
              class: "k-status-flag k-status-flag-" + page.status,
              icon: page.permissions.changeStatus === false ? "protected" : "circle",
              disabled: page.permissions.changeStatus === false,
              click: () => {
                this.action(page, "status");
              }
            };

            return page;
          });

          this.add        = response.options.add && this.$permissions.pages.create;
          this.pagination = response.pagination;
          this.headline   = response.options.headline || " ";
          this.sortable   = response.options.sortable;
          this.min        = response.options.min;
          this.max        = response.options.max;
          this.layout     = response.options.layout || "list";
          this.link       = response.options.link;
          this.error      = response.errors[0];
          this.isLoading  = false;
        })
        .catch(error => {
          this.isLoading = false;
          this.issue = error.message;
        });
    },
    paginate(pagination) {
      this.page = pagination.page;
      this.fetch();
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

        this.$api.pages.status(element.id, "listed", position)
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
    update() {
      this.fetch();
      this.$events.$emit("model.update");
    }
  }
};
</script>
