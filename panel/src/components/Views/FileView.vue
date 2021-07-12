<template>
  <k-inside :lock="lock">
    <div
      :data-locked="isLocked"
      :data-id="model.id"
      :data-template="blueprint"
      class="k-file-view"
    >
      <k-file-preview :file="model" />

      <k-view class="k-file-content">
        <k-header
          :editable="permissions.changeName && !isLocked"
          :tab="tab.name"
          :tabs="tabs"
          @edit="$dialog($view.path + '/changeName')"
        >
          {{ model.filename }}

          <template #left>
            <k-button-group>
              <k-button
                :link="model.url"
                :responsive="true"
                class="k-file-view-options"
                icon="open"
                target="_blank"
              >
                {{ $t("open") }}
              </k-button>
              <k-dropdown class="k-file-view-options">
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
          :empty="$t('file.blueprint', { blueprint: $esc(blueprint) })"
          :lock="lock"
          :parent="path"
          :tab="tab"
        />

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
        case "replace":
          this.$refs.upload.open({
            url: this.$urls.api + "/" + this.$api.files.url(this.model.parent, this.model.filename),
            accept: "." + this.model.extension + "," + this.model.mime
          });
          break;
      }
    },
    onUpload() {
      this.$store.dispatch("notification/success", ":)");
      this.$reload();
    }
  }
};
</script>
