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
        :parent="$api.files.url(path, file.filename)"
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
import PrevNext from "@/mixins/prevnext.js";
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
    },
    tabsKey() {
      return "file-" + this.file.id + "-tabs";
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
    fetch() {
      this.$api.files
        .get(this.path, this.filename, { view: "panel" })
        .then(file => {
          this.file = file;
          this.file.next = file.nextWithTemplate;
          this.file.prev = file.prevWithTemplate;
          this.file.url = file.url;
          this.name = file.name;
          this.tabs = file.blueprint.tabs;
          this.permissions = file.options;
          this.options = ready => {
            this.$api.files
              .options(this.path, this.file.filename)
              .then(options => {
                ready(options);
              });
          };

          this.$store.dispatch("breadcrumb", this.$api.files.breadcrumb(this.file, this.$route.name));
          this.$store.dispatch("title", this.filename);
          this.$store.dispatch("form/create", {
            id: "files/" + file.id,
            api: this.$api.files.link(this.path, this.filename),
            content: file.content
          });

        })
        .catch(error => {
          window.console.error(error);
          this.issue = error;
        });
    },
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
              this.$api.files.url(this.path, this.file.filename),
            accept: this.file.mime
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
    renamed(file) {
      this.$router.push(this.$api.files.link(this.path, file.filename));
    },
    uploaded() {
      this.fetch();
      this.$store.dispatch("notification/success", ":)");
    }
  }
};
</script>
