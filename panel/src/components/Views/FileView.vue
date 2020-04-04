<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <div v-else class="k-file-view">
    <k-file-preview :file="file" />

    <k-view :data-locked="isLocked" class="k-file-content">
      <k-header
        :editable="permissions.changeName && !isLocked"
        :tabs="tabs"
        :tab="tab"
        @edit="action('rename')"
      >
        {{ file.filename }}

        <k-button-group slot="left">
          <k-button :responsive="true" icon="open" @click="action('download')">
            {{ $t("open") }}
          </k-button>
          <k-dropdown>
            <k-button
              :responsive="true"
              :disabled="isLocked"
              icon="cog"
              @click="$refs.settings.toggle()"
            >
              {{ $t('settings') }}
            </k-button>
            <k-dropdown-content ref="settings" :options="options" @action="action" />
          </k-dropdown>
          <k-languages-dropdown />
        </k-button-group>

        <k-prev-next
          v-if="file.id"
          slot="right"
          :prev="prev"
          :next="next"
        />
      </k-header>

      <k-tabs
        v-if="file.id"
        ref="tabs"
        :key="tabsKey"
        :parent="$model.files.url(path, file.filename)"
        :tabs="tabs"
        :blueprint="file.blueprint.name"
        @tab="tab = $event"
      />

      <k-file-rename-dialog ref="rename" @success="renamed" />
      <k-file-remove-dialog ref="remove" @success="deleted" />
      <k-upload
        ref="upload"
        :url="uploadApi"
        :accept="file.mime"
        :multiple="false"
        @success="uploaded"
      />
    </k-view>
  </div>
</template>

<script>
import PrevNext from "@/mixins/view/prevnext.js";
import config from "@/config/config.js";

export default {
  mixins: [PrevNext],
  props: {
    path: {
      type: String
    },
    filename: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      name: "",
      file: {
        id: null,
        parent: null,
        filename: "",
        url: "",
        prev: null,
        next: null,
        panelIcon: null,
        panelImage: null,
        mime: null,
        content: {}
      },
      permissions: {
        changeName: false,
        delete: false
      },
      issue: null,
      tabs: [],
      tab: null,
      options: null
    };
  },
  computed: {
    language() {
      return this.$store.state.languages.current;
    },
    next() {
      if (this.file.next) {
        return {
          link: this.$model.files.link(
            this.path,
            this.file.next.filename
          ),
          tooltip: this.file.next.filename
        };
      }
    },
    prev() {
      if (this.file.prev) {
        return {
          link: this.$model.files.link(
            this.path,
            this.file.prev.filename
          ),
          tooltip: this.file.prev.filename
        };
      }
    },
    tabsKey() {
      return "file-" + this.file.id + "-tabs";
    },
    uploadApi() {
      return config.api + "/" + this.path + "/files/" + this.filename;
    },
  },
  watch: {
    language() {
      this.fetch();
    },
    filename() {
      this.fetch();
    }
  },
  methods: {
    action(action) {
      switch (action) {
        case "download":
          window.open(this.file.url);
          break;
        case "rename":
          this.$refs.rename.open(this.path, this.file.filename);
          break;
        case "replace":
          this.$refs.upload.open({
            url:
              config.api +
              "/" +
              this.$model.files.url(this.path, this.file.filename),
            accept: "." + this.file.extension + "," + this.file.mime
          });
          break;
        case "remove":
          this.$refs.remove.open(this.path, this.file.filename);
          break;
      }
    },
    deleted() {
      if (this.path) {
        this.$router.push('/' + this.path);
      } else {
        this.$router.push('/site');
      }
    },
    async fetch() {
      try {
        this.file = await this.$api.files.get( this.path, this.filename, {
          view: "panel"
        });

        this.file.next   = this.file.nextWithTemplate;
        this.file.prev   = this.file.prevWithTemplate;
        this.name        = this.file.name;
        this.tabs        = this.file.blueprint.tabs;
        this.permissions = this.file.options;

        this.options = async ready => {
          let options = await this.$model.files.options(
            this.path,
            this.file.filename
          );
          ready(options);
        };

        this.$store.dispatch(
          "breadcrumb",
          this.$model.files.breadcrumb(this.file, this.$route.name)
        );
        this.$store.dispatch("title", this.filename);
        this.$store.dispatch("content/create", {
          id: "files/" + this.file.id,
          api: this.$model.files.link(this.path, this.filename),
          content: this.file.content
        });
      } catch (error) {
        this.issue = error;
      }
    },
    renamed(file) {
      const path = this.$model.files.link(this.path, file.filename);
      this.$router.push(path);
    },
    async uploaded() {
      this.$store.dispatch("notification/success", ":)");
      await this.fetch();
      this.$events.$emit("file.uploaded", this.file);
    }
  }
};
</script>
