<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <div v-else-if="file.id !== null" class="k-file-view">
    <k-file-preview :file="file" />

    <k-view :data-locked="isLocked" class="k-file-content">
      <k-header
        :editable="permissions.changeName && !isLocked"
        :tab="tab"
        :tabs="tabs"
        @edit="action('rename')"
      >
        {{ file.filename }}

        <k-button-group slot="left">
          <k-button
            :link="file.url"
            :responsive="true"
            icon="open"
            target="_blank"
          >
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

      <k-sections
        v-if="file.id"
        :blueprint="file.blueprint.name"
        :empty="$t('file.blueprint', { template: $esc(file.blueprint.name) })"
        :parent="parent"
        :tab="tab"
        :tabs="tabs"
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
    },
    tab: {
      type: String,
      required: true
    }
  },
  data() {
    return {
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
      parent: null,
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
      return config.api + "/" + this.path + "/files/" + this.filename;
    },
    prev() {
      if (this.file.prev) {
        return {
          link: this.$api.files.link(
            this.path,
            this.file.prev.filename
          ),
          tooltip: this.file.prev.filename
        };
      }

      return null;
    },
    language() {
      return this.$store.state.languages.current;
    },
    next() {
      if (this.file.next) {
        return {
          link: this.$api.files.link(
            this.path,
            this.file.next.filename
          ),
          tooltip: this.file.next.filename
        };
      }

      return null;
    }
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
    async fetch() {
      try {
        const file = await this.$api.files.get(
          this.path,
          this.filename,
          { view: "panel" }
        );

        this.file = {
          ...file,
          next: file.nextWithTemplate,
          prev: file.prevWithTemplate,
          url:  file.url
        }

        this.parent = this.$api.files.url(this.path, file.filename);
        this.tabs = file.blueprint.tabs;
        this.permissions = file.options;

        this.options = async ready => {
          const options = await this.$api.files.options(
            this.path,
            this.file.filename
          );

          ready(options);
        };

        this.$store.dispatch("breadcrumb", this.$api.files.breadcrumb(this.file, this.$route.name));
        this.$store.dispatch("title", this.filename);
        this.$store.dispatch("content/create", {
          id: "files/" + file.id,
          api: this.$api.files.link(this.path, this.filename),
          content: file.content
        });

      } catch (error) {
        window.console.error(error);
        this.issue = error;
      }
    },
    action(action) {
      switch (action) {
        case "rename":
          this.$refs.rename.open(this.path, this.file.filename);
          break;
        case "replace":
          this.$refs.upload.open({
            url:
              config.api +
              "/" +
              this.$api.files.url(this.path, this.file.filename),
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
        this.$go('/' + this.path);
      } else {
        this.$go('/site');
      }
    },
    renamed(file) {
      this.$go(this.$api.files.link(this.path, file.filename));
    },
    uploaded() {
      this.fetch();
      this.$store.dispatch("notification/success", ":)");
    }
  }
};
</script>
