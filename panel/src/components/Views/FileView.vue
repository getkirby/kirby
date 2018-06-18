<template>

  <kirby-error-view v-if="issue">
    {{ issue.message }}
  </kirby-error-view>
  <div v-else class="kirby-file-view">

    <kirby-file-preview :file="file" />

    <kirby-view class="kirby-file-content">

      <kirby-header :editable="permissions.changeName" @edit="action('rename')">

        {{ file.filename }}

        <kirby-button-group slot="left">
          <kirby-button icon="open" @click="action('download')">
            {{ $t("open") }}
          </kirby-button>
          <kirby-dropdown>
            <kirby-button icon="cog" @click="$refs.settings.toggle()">
              {{ $t('settings') }}
            </kirby-button>
            <kirby-dropdown-content ref="settings" :options="options" @action="action" />
          </kirby-dropdown>
          <kirby-tabs-dropdown v-if="tabs.length > 1" :tabs="tabs" @open="$refs.tabs.open($event)" />
        </kirby-button-group>

        <kirby-button-group v-if="file.id" slot="right">
          <kirby-button :disabled="!prev" v-bind="prev" icon="angle-left" />
          <kirby-button :disabled="!next" v-bind="next" icon="angle-right" />
        </kirby-button-group>

      </kirby-header>

      <kirby-tabs
        v-if="file.id"
        ref="tabs"
        :key="'file-' + file.id + '-tabs'"
        :parent="$api.file.url(file.parent.id, file.filename)"
        :tabs="tabs"
      />

      <kirby-file-rename-dialog ref="rename" />
      <kirby-file-remove-dialog ref="remove" @success="$router.push('/pages/' + path)" />
      <kirby-upload
        ref="upload"
        :url="uploadApi"
        :accept="file.mime"
        :multiple="false"
        @success="uploaded"
      />

    </kirby-view>
  </div>

</template>

<script>
import PrevNext from "@/mixins/prevnext.js";
import slug from "@/ui/helpers/slug.js";
import config from "@/config/config.js";

export default {
  mixins: [PrevNext],
  props: {
    path: {
      type: String,
      required: true
    },
    filename: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      name: "",
      preview: {},
      file: {
        id: null,
        parent: null,
        filename: "",
        url: "",
        prev: null,
        next: null,
        mime: null,
        content: {}
      },
      permissions: {
        changeName: false,
        delete: false
      },
      issue: null,
      tabs: [],
      options: null
    };
  },
  computed: {
    uploadApi() {
      return config.api + "/pages/" + this.path + "/files/" + this.filename;
    },
    prev() {
      if (this.file.prev) {
        return {
          link: this.$api.file.link(
            this.file.parent.id,
            this.file.prev.filename
          ),
          tooltip: this.file.prev.filename
        };
      }
    },
    next() {
      if (this.file.next) {
        return {
          link: this.$api.file.link(
            this.file.parent.id,
            this.file.next.filename
          ),
          tooltip: this.file.next.filename
        };
      }
    }
  },
  methods: {
    fetch() {
      this.$api.file
        .get(this.path, this.filename, { view: "panel" })
        .then(file => {
          this.file = file;
          this.file.url = file.url + "?v=" + file.modified;
          this.name = file.name;
          this.tabs = file.blueprint.tabs;
          this.permissions = file.blueprint.options;
          this.preview = this.$api.file.preview(file);
          this.options = ready => {
            this.$api.file
              .options(this.file.parent.id, this.file.filename)
              .then(options => {
                ready(options);
              });
          };

          this.$store.dispatch("breadcrumb", this.$api.file.breadcrumb(file));
          this.$store.dispatch("title", this.filename);
        })
        .catch(error => {
          console.error(error);
          this.issue = error;
        });
    },
    action(action) {
      switch (action) {
        case "download":
          window.open(this.file.url);
          break;
        case "rename":
          this.$refs.rename.open(this.file.parent.id, this.file.filename);
          break;
        case "replace":
          this.$refs.upload.open({
            url:
              config.api +
              "/" +
              this.$api.file.url(this.file.parent.id, this.file.filename),
            accept: this.file.mime
          });
          break;
        case "remove":
          this.$refs.remove.open(this.file.parent.id, this.file.filename);
          break;
      }
    },
    updateFilename(name) {
      name = slug(name);

      if (name.length === 0) {
        this.$store.dispatch("alert", this.$t("error.file.name.missing"));
        return;
      }

      if (name === this.name) {
        return true;
      }

      this.$api.file.rename(this.path, this.file.filename, name).then(file => {
        this.$router.push("/pages/" + this.path + "/files/" + file.filename);
        this.$store.dispatch("notification/success", this.$t("file.renamed"));
      });
    },
    uploaded() {
      this.fetch();
      this.$store.dispatch("notification/success", this.$t("file.replaced"));
    }
  }
};
</script>
