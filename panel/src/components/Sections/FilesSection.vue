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
        <k-empty v-else icon="image" @click="if (add) upload()">
          {{ $t('files.empty') }}
        </k-empty>
      </k-dropzone>

      <k-file-rename-dialog ref="rename" @success="update" />
      <k-file-remove-dialog ref="remove" @success="update" />
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
      add: false,
      data: [],
      error: false,
      headline: null,
      isLoading: true,
      min: null,
      issue: false,
      layout: "list",
      page: 1,
      pagination: {},
    };
  },
  computed: {
    uploadParams() {

      if (this.add === false) {
        return false;
      }

      return {
        ...this.add,
        url: config.api + "/" + this.add.api
      };

    }
  },
  created() {
    this.fetch();
    this.$events.$on("model.update", this.fetch);
  },
  destroyed() {
    this.$events.$off("model.update", this.fetch);
  },
  methods: {
    fetch() {
      this.$api
        .get(this.parent + "/sections/" + this.name, { page: this.page })
        .then(response => {

          this.headline   = response.options.headline || " ";
          this.min        = response.options.min;
          this.pagination = response.pagination;
          this.sortable   = response.options.sortable === true && response.data.length > 1;
          this.layout     = response.options.layout || "list";
          this.isLoading  = false;

          if (this.$permissions.files.create && response.options.upload !== false) {
            this.add = response.options.upload;
          } else {
            this.add = false;
          }

          this.data = response.data.map(file => {
            file.options = ready => {
              this.$api.files
                .options(file.parent, file.filename, "list")
                .then(options => ready(options))
                .catch(error => {
                  this.$store.dispatch("notification/error", error);
                });
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
        .patch(this.parent + "/files/sort", { files: items })
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
    update() {
      this.$events.$emit("model.update");
    },
    uploaded() {
      this.$events.$emit("file.create");
      this.$events.$emit("model.update");
      this.$store.dispatch("notification/success", this.$t("file.uploaded"));
    },
    paginate(pagination) {
      this.page = pagination.page;
      this.fetch();
    }
  }
};
</script>
