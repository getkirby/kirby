<template>

  <section v-if="isLoading === false" class="kirby-files-section">

    <header class="kirby-section-header">
      <kirby-headline>
        {{ headline }} <abbr v-if="min" title="This section is required">*</abbr>
      </kirby-headline>
      <kirby-button-group v-if="add">
        <kirby-button icon="upload" @click="upload">{{ $t("add") }}</kirby-button>
      </kirby-button-group>
    </header>

    <template v-if="issue">
      <kirby-box theme="negative">
        <kirby-text size="small">
          <strong>{{ $t("error.blueprint.section.notLoaded", {name: name}) }}:</strong>
          {{ issue }}
        </kirby-text>
      </kirby-box>
    </template>

    <template v-else>
      <kirby-dropzone :disabled="add === false" @drop="drop">
        <kirby-collection
          :layout="layout"
          :items="data"
          :pagination="pagination"
          :sortable="sortable"
          :draggable="true"
          @sort="sort"
          @paginate="paginate"
          @action="action"
        />
      </kirby-dropzone>

      <kirby-file-rename-dialog ref="rename" @success="fetch" />
      <kirby-file-remove-dialog ref="remove" @success="fetch" />
      <kirby-upload ref="upload" @success="uploaded" @error="fetch" />
    </template>

  </section>
</template>

<script>
import config from "@/config/config.js";
import Section from "@/mixins/section.js";

export default {
  mixins: [Section],
  data() {
    return {
      accept: "*",
      add: false,
      data: [],
      error: false,
      headline: null,
      isLoading: true,
      min: null,
      max: null,
      issue: false,
      layout: "list",
      page: 1,
      pagination: {}
    };
  },
  computed: {
    uploadParams() {
      return {
        url: config.api + "/" + this.parent + "/" + this.name,
        accept: this.accept
      };
    }
  },
  created() {
    this.fetch();
  },
  mounted() {
    this.$events.$on("file.create", this.fetch);
    this.$events.$on("file.delete", this.fetch);
  },
  destroyed() {
    this.$events.$off("file.create", this.fetch);
    this.$events.$off("file.delete", this.fetch);
  },
  methods: {
    fetch() {
      this.$api
        .get(this.parent + "/" + this.name, { page: this.page })
        .then(response => {
          this.data = response.data.map(file => {
            file.options = ready => {
              this.$api.files
                .options(file.parent, file.filename, "list")
                .then(options => ready(options));
            };

            return file;
          });

          this.accept     = response.options.accept || "*";
          this.error      = response.options.errors[0];
          this.headline   = response.options.headline;
          this.add        = response.options.add && this.$permissions.files.create;
          this.min        = response.options.min;
          this.max        = response.options.max;
          this.template   = response.options.template;
          this.pagination = response.pagination;
          this.sortable   = response.options.sortable === true && this.data.length > 1;
          this.layout     = response.options.layout || "list";
          this.isLoading  = false;
        })
        .catch(error => {
          this.isLoading = false;
          this.issue = error.message;
        });
    },
    sort(items) {
      if (this.sortable === false) {
        return false;
      }

      items = items.map(item => {
        return item.id;
      });

      this.$api
        .patch(this.parent + "/" + this.name + "/sort", { items })
        .then(() => {
          this.$store.dispatch("notification/success", this.$t("file.sorted"));
        })
        .catch(response => {
          this.fetch();
          this.$store.dispatch("notification/error", response.message);
        });
    },
    action(file, action) {
      switch (action) {
        case "edit":
          this.$router.push(file.link);
          break;
        case "download":
          window.open(file.url);
          break;
        case "rename":
          this.$refs.rename.open(file.parent, file.filename);
          break;
        case "replace":
          this.replace(file);
          break;
        case "remove":
          this.$refs.remove.open(file.parent, file.filename);
          break;
      }
    },
    drop(files) {
      this.$refs.upload.drop(files, this.uploadParams);
    },
    upload() {
      this.$refs.upload.open(this.uploadParams);
    },
    replace(file) {
      this.$refs.upload.open({
        url: config.api + "/" + this.$api.files.url(file.parent, file.filename),
        accept: file.mime,
        multiple: false
      });
    },
    uploaded() {
      this.fetch();
      this.$events.$emit("file.create");
      this.$store.dispatch("notification/success", this.$t("file.uploaded"));
    },
    paginate(pagination) {
      this.page = pagination.page;
      this.fetch();
    }
  }
};
</script>
