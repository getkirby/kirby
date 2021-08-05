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
          @edit="$dialog(id + '/changeName')"
        >
          {{ model.filename }}

          <template #left>
            <k-button-group>
              <k-button
                :link="model.previewUrl"
                :responsive="true"
                :text="$t('open')"
                icon="open"
                target="_blank"
                class="k-file-view-options"
              />
              <k-dropdown class="k-file-view-options">
                <k-button
                  :disabled="isLocked"
                  :responsive="true"
                  :text="$t('settings')"
                  icon="cog"
                  @click="$refs.settings.toggle()"
                />
                <k-dropdown-content
                  ref="settings"
                  :options="$dropdown(id)"
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
          :parent="id"
          :tab="tab"
        />

        <k-upload
          ref="upload"
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
      return this.$api.files.url(this.model.parent, this.model.filename);
    }
  },
  methods: {
    action(action) {
      switch (action) {
        case "replace":
          this.$refs.upload.open({
            url: this.$urls.api + "/" + this.id,
            accept: "." + this.model.extension + "," + this.model.mime,
            multiple: false
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
