<template>
  <k-inside>
    <div class="k-file-view">
      <k-file-preview :file="file" />

      <k-view :data-locked="isLocked" class="k-file-content">
        <k-header
          :editable="permissions.changeName && !isLocked"
          :tab="tab.name"
          :tabs="tabs"
          @edit="action('rename')"
        >
          {{ file.filename }}

          <template #left>
            <k-button-group>
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
          </template>

          <template #right>
            <k-prev-next
              v-if="file.id"
              :prev="prev"
              :next="next"
            />
          </template>
        </k-header>

        <k-sections
          :blueprint="file.blueprint.name"
          :empty="$t('file.blueprint', { template: file.blueprint.name })"
          :parent="parent"
          :tab="tab"
        />

        <k-file-rename-dialog ref="rename" @success="onRename" />
        <k-file-remove-dialog ref="remove" @success="onDelete" />
        <k-upload
          ref="upload"
          :url="uploadApi"
          :accept="file.mime"
          :multiple="false"
          @success="onUpload"
        />
      </k-view>
    </div>
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  extends: ModelView,
  props: {
    file: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  computed: {
    options() {
      return async ready => {
        const options = await this.$api.files.options(
          this.file.parent,
          this.file.filename
        );
        ready(options);
      };
    },
    uploadApi() {
      return this.$urls.api + "/" + this.file.parent + "/files/" + this.file.filename;
    },
  },
  watch: {
    "file.id": {
      handler() {
        this.$store.dispatch("content/create", {
          id: "files/" + this.file.id,
          api: this.$api.files.link(this.file.parent, this.file.filename),
          content: this.file.content
        });
      },
      immediate: true
    }
  },
  methods: {
    action(action) {
      switch (action) {
        case "rename":
          this.$refs.rename.open(this.file.parent, this.file.filename);
          break;
        case "replace":
          this.$refs.upload.open({
            url: this.$urls.api + "/" + this.$api.files.url(this.file.parent, this.file.filename),
            accept: "." + this.file.extension + "," + this.file.mime
          });
          break;
        case "remove":
          this.$refs.remove.open(this.file.parent, this.file.filename);
          break;
      }
    },
    onDeleted() {
      if (this.file.parent) {
        this.$go('/' + this.file.parent);
      } else {
        this.$go('/site');
      }
    },
    onRenam(file) {
      if (file.filename !== this.file.filename) {
        this.$go(this.$api.files.link(this.file.parent, file.filename));
      }
    },
    onUpload() {
      this.$store.dispatch("notification/success", ":)");
      this.$reload();
    }
  }
};
</script>
