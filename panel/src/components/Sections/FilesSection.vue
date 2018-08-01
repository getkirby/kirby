<template>

  <section v-if="isLoading === false" class="k-files-section">

    <header class="k-section-header">
      <k-headline>
        {{ headline }} <abbr v-if="min" title="This section is required">*</abbr>
      </k-headline>
      <k-button-group v-if="add">
        <k-button icon="upload" @click="upload">{{ $t("add") }}</k-button>
      </k-button-group>
    </header>

    <template v-if="issue">
      <k-box theme="negative">
        <k-text size="small">
          <strong>{{ $t("error.blueprint.section.notLoaded", {name: name}) }}:</strong>
          {{ issue }}
        </k-text>
      </k-box>
    </template>

    <template v-else>
      <k-dropzone :disabled="add === false" @drop="drop">
        <k-collection
          v-if="data.length"
          :layout="layout"
          :items="data"
          :pagination="pagination"
          :sortable="sortable"
          @sort="sort"
          @paginate="paginate"
          @action="action"
        />
        <k-box v-else theme="empty">
          <k-icon type="image" size="medium" />
          <p>{{ $t('files.empty') }}</p>
        </k-box>
      </k-dropzone>

      <k-file-rename-dialog ref="rename" @success="fetch" />
      <k-file-remove-dialog ref="remove" @success="fetch" />
      <k-upload ref="upload" @success="uploaded" @error="fetch" />
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
      pagination: {},
      template: null,
    };
  },
  computed: {
    uploadParams() {
      return {
        url: config.api + "/" + this.parent + "/files",
        accept: this.accept,
        attributes: {
          template: this.template
        }
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
        .get(this.parent + "/sections/" + this.name, { page: this.page })
        .then(response => {

          this.accept     = response.options.accept || "*";
          this.error      = response.options.errors[0];
          this.headline   = response.options.headline || " ";
          this.add        = response.options.add && this.$permissions.files.create;
          this.min        = response.options.min;
          this.max        = response.options.max;
          this.template   = response.options.template;
          this.pagination = response.pagination;
          this.sortable   = response.options.sortable === true && response.data.length > 1;
          this.layout     = response.options.layout || "list";
          this.isLoading  = false;


          this.data = response.data.map(file => {
            file.options = ready => {
              this.$api.files
                .options(file.parent, file.filename, "list")
                .then(options => ready(options));
            };

            file.sortable = this.sortable;

            return file;
          });

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
        .patch(this.parent + "/sections/" + this.name + "/sort", { items })
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
