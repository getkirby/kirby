<template>
  <section
    v-if="isLoading === false"
    :data-processing="isProcessing"
    class="k-files-section"
  >
    <header class="k-section-header">
      <k-headline>
        {{ headline }} <abbr v-if="options.min" :title="$t('section.required')">*</abbr>
      </k-headline>
      <k-button-group v-if="add">
        <k-button icon="upload" @click="upload">
          {{ $t("add") }}
        </k-button>
      </k-button-group>
    </header>

    <template v-if="error">
      <k-box theme="negative">
        <k-text size="small">
          <strong>{{ $t("error.section.notLoaded", {name: name}) }}:</strong>
          {{ error }}
        </k-text>
      </k-box>
    </template>

    <template v-else>
      <k-dropzone :disabled="add === false" @drop="drop">
        <k-collection
          v-if="data.length"
          :help="help"
          :items="data"
          :layout="options.layout"
          :pagination="pagination"
          :sortable="!isProcessing && options.sortable"
          :size="options.size"
          :data-invalid="isInvalid"
          @sort="sort"
          @paginate="paginate"
          @action="action"
        />
        <template v-else>
          <k-empty
            :layout="options.layout"
            :data-invalid="isInvalid"
            icon="image"
            @click="if (add) upload()"
          >
            {{ options.empty || $t('files.empty') }}
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
      </k-dropzone>

      <k-file-rename-dialog ref="rename" @success="update" />
      <k-file-remove-dialog ref="remove" @success="update" />
      <k-file-sort-dialog ref="sort" @success="reload" />
      <k-upload ref="upload" @success="uploaded" @error="reload" />
    </template>
  </section>
</template>

<script>
import config from "@/config/config.js";
import CollectionSectionMixin from "@/mixins/section/collection.js";

export default {
  mixins: [CollectionSectionMixin],
  computed: {
    add() {
      if (this.$permissions.files.create && this.options.upload !== false) {
        return this.options.upload;
      } else {
        return false;
      }
    },
  },
  created() {
    this.load();
    this.$events.$on("model.update", this.reload);
  },
  destroyed() {
    this.$events.$off("model.update", this.reload);
  },
  methods: {
    action(file, action) {

      switch (action) {
        case "edit":
          this.$go(file.link);
          break;
        case "download":
          window.open(file.url);
          break;
        case "rename":
          this.$refs.rename.open(file.parent, file.filename);
          break;
        case "replace":
          this.$refs.upload.open({
            url: config.api + "/" + this.$api.files.url(file.parent, file.filename),
            accept: "." + file.extension + "," + file.mime,
            multiple: false
          });
          break;
        case "remove":
          if (this.data.length <= this.options.min) {
            const number = this.options.min > 1 ? "plural" : "singular";
            this.$store.dispatch("notification/error", {
              message: this.$t("error.section.files.min." + number, {
                section: this.options.headline || this.name,
                min: this.options.min
              })
            });
            break;
          }

          this.$refs.remove.open(file.parent, file.filename);
          break;
        case "sort":
          this.$refs.sort.open(file.parent, file, this.options.apiUrl);
          break;
      }

    },
    drop(files) {
      if (this.add === false) {
        return false;
      }

      this.$refs.upload.drop(files, {
        ...this.add,
        url: config.api + "/" + this.add.api
      });
    },
    items(data) {
      return data.map(file => {
        file.sortable = this.options.sortable;
        file.column   = this.column;
        file.options  = async ready => {
          try {
            const options = await this.$api.files.options(
              file.parent,
              file.filename,
              "list",
              this.options.sortable
            );
            ready(options);

          } catch (error) {
            this.$store.dispatch("notification/error", error);
          }
        };

        return file;
      });
    },
    replace(file) {
      this.$refs.upload.open({
        url: config.api + "/" + this.$api.files.url(file.parent, file.filename),
        accept: file.mime,
        multiple: false
      });
    },
    async sort(items) {
      if (this.options.sortable === false) {
        return false;
      }

      this.isProcessing = true;

      items = items.map(item => {
        return item.id;
      });

      try {
        await this.$api.patch(this.options.apiUrl + "/files/sort", {
          files: items,
          index: this.pagination.offset
        });
        this.$store.dispatch("notification/success", ":)");

      } catch (error) {
        this.reload();
        this.$store.dispatch("notification/error", error.message);

      } finally {
        this.isProcessing = false
      }
    },
    update() {
      this.$events.$emit("model.update");
    },
    upload() {
      if (this.add === false) {
        return false;
      }

      this.$refs.upload.open({
        ...this.add,
        url: config.api + "/" + this.add.api
      });
    },
    uploaded() {
      this.$events.$emit("file.create");
      this.$events.$emit("model.update");
      this.$store.dispatch("notification/success", ":)");
    }
  }
};
</script>

<style>
.k-files-section[data-processing] {
  pointer-events: none;
}
</style>
