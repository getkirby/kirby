<template>
  <k-inside :lock="lock">
    <div class="k-file-view">
      <k-file-preview :file="model" />

      <k-view :data-locked="isLocked" class="k-file-content">
        <k-header
          :editable="permissions.changeName && !isLocked"
          :tab="tab.name"
          :tabs="tabs"
          @edit="action('rename')"
        >
          {{ model.filename }}

          <template #left>
            <k-button-group>
              <k-button
                :link="model.url"
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
                <k-dropdown-content
                  ref="settings"
                  :options="options"
                  @action="action"
                />
              </k-dropdown>
              <k-languages-dropdown />
            </k-button-group>
          </template>

          <template #right>
            <k-prev-next
              :prev="prev"
              :next="next"
            />
          </template>
        </k-header>

        <k-sections
          :blueprint="blueprint"
          :empty="$t('file.blueprint', { template: blueprint })"
          :lock="lock"
          :parent="path"
          :tab="tab"
        />

        <k-file-rename-dialog ref="rename" @success="onRename" />

        <k-upload
          ref="upload"
          :url="uploadApi"
          :accept="model.mime"
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
  computed: {
    id() {
      return "files/" + this.model.id;
    },
    options() {
      return async ready => {
        const options = await this.$api.files.options(
          this.model.parent,
          this.model.filename
        );
        ready(options);
      };
    },
    path() {
      return this.model.parent + "/files/" + this.model.filename;
    },
    uploadApi() {
      return this.$urls.api + "/" + this.path;
    },
  },
  methods: {
    action(action) {
      switch (action) {
        case "rename":
          this.$refs.rename.open(this.model.parent, this.model.filename);
          break;
        case "replace":
          this.$refs.upload.open({
            url: this.$urls.api + "/" + this.$api.files.url(this.model.parent, this.model.filename),
            accept: "." + this.model.extension + "," + this.model.mime
          });
          break;
      }
    },
    onDelete() {
      if (this.model.parent) {
        this.$go('/' + this.model.parent);
      } else {
        this.$go('/site');
      }
    },
    onRename(file) {
      if (file.filename !== this.model.filename) {
        this.$go(this.$api.files.link(this.model.parent, file.filename));
      }
    },
    onUpload() {
      this.$store.dispatch("notification/success", ":)");
      this.$reload();
    }
  }
};
</script>
