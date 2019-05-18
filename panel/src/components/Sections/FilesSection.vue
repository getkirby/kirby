<template>

  <section v-if="isLoading === false" class="k-files-section">

    <header class="k-section-header">
      <k-headline>
        {{ headline }} <abbr v-if="options.min" title="This section is required">*</abbr>
      </k-headline>
      <k-button-group v-if="add">
        <k-button icon="upload" @click="upload">{{ $t("add") }}</k-button>
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
          :sortable="options.sortable"
          :size="options.size"
          @sort="sort"
          @paginate="paginate"
          @action="action"
        />
        <template v-else>
          <k-empty
            :layout="options.layout"
            icon="image"
            @click="if (add) upload()"
          >
            {{ options.empty || $t('files.empty') }}
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
      </k-dropzone>

      <k-file-rename-dialog ref="rename" @success="update" />
      <k-file-remove-dialog ref="remove" @success="update" />
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
    }
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
      // check first if file is locked
      const url = this.$api.files.url(file.parent, file.filename, "lock");
      this.$api.get(url).then(response => {

        // restrict actions if file is locked
        if (response.locked && ["download", "edit"].includes(action) === false) {
          this.$store.dispatch('notification/error', this.$t("lock.file.isLocked", { email: response.email }));
          return;
        }

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
        }
      });
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
        file.options = ready => {
          this.$api.files
            .options(file.parent, file.filename, "list")
            .then(options => ready(options))
            .catch(error => {
              this.$store.dispatch("notification/error", error);
            });
        };

        file.sortable = this.options.sortable;

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
    sort(items) {
      if (this.options.sortable === false) {
        return false;
      }

      items = items.map(item => {
        return item.id;
      });

      this.$api
        .patch(this.parent + "/files/sort", {
          files: items,
          index: this.pagination.offset
        })
        .then(() => {
          this.$store.dispatch("notification/success", ":)");
        })
        .catch(response => {
          this.reload();
          this.$store.dispatch("notification/error", response.message);
        });
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
