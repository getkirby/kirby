<template>
  <k-inside :lock="lock">
    <div
      :data-locked="isLocked"
      :data-id="model.id"
      :data-template="blueprint"
      class="k-user-view"
    >
      <div class="k-user-profile">
        <k-view>
          <template v-if="model.avatar">
            <k-dropdown>
              <k-button
                :tooltip="$t('avatar')"
                :disabled="isLocked"
                class="k-user-view-image"
                @click="$refs.picture.toggle()"
              >
                <k-image
                  v-if="model.avatar"
                  :cover="true"
                  :src="model.avatar"
                  ratio="1/1"
                />
              </k-button>
              <k-dropdown-content ref="picture">
                <k-dropdown-item icon="upload" @click="$refs.upload.open()">
                  {{ $t('change') }}
                </k-dropdown-item>
                <k-dropdown-item icon="trash" @click="deleteAvatar">
                  {{ $t('delete') }}
                </k-dropdown-item>
              </k-dropdown-content>
            </k-dropdown>
          </template>
          <template v-else>
            <k-button
              :tooltip="$t('avatar')"
              class="k-user-view-image"
              @click="$refs.upload.open()"
            >
              <k-icon type="user" />
            </k-button>
          </template>

          <k-button-group>
            <k-button
              :disabled="!permissions.changeEmail || isLocked"
              icon="email"
              @click="$dialog(id + '/changeEmail')"
            >
              {{ $t("email") }}: {{ model.email }}
            </k-button>
            <k-button
              :disabled="!permissions.changeRole || isLocked"
              icon="bolt"
              @click="$dialog(id + '/changeRole')"
            >
              {{ $t("role") }}: {{ model.role }}
            </k-button>
            <k-button
              :disabled="!permissions.changeLanguage || isLocked"
              icon="globe"
              @click="$dialog(id + '/changeLanguage')"
            >
              {{ $t("language") }}: {{ model.language }}
            </k-button>
          </k-button-group>
        </k-view>
      </div>

      <k-view>
        <k-header
          :editable="permissions.changeName && !isLocked"
          :tab="tab.name"
          :tabs="tabs"
          @edit="$dialog(id + '/changeName')"
        >
          <span v-if="!model.name || model.name.length === 0" class="k-user-name-placeholder">{{ $t("name") }} …</span>
          <template v-else>
            {{ model.name }}
          </template>

          <template #left>
            <k-button-group>
              <k-dropdown class="k-user-view-options">
                <k-button :disabled="isLocked" icon="cog" @click="$refs.settings.toggle()">
                  {{ $t('settings') }}
                </k-button>
                <k-dropdown-content
                  ref="settings"
                  :options="$dropdown(id)"
                />
              </k-dropdown>
              <k-languages-dropdown />
            </k-button-group>
          </template>

          <template #right>
            <k-prev-next
              v-if="$options.prevnext"
              :prev="prev"
              :next="next"
            />
          </template>
        </k-header>

        <k-sections
          :blueprint="blueprint"
          :empty="$t('user.blueprint', { blueprint: $esc(blueprint) })"
          :lock="lock"
          :parent="id"
          :tab="tab"
        />

        <k-upload
          ref="upload"
          :url="uploadApi"
          :multiple="false"
          accept="image/*"
          @success="uploadedAvatar"
        />
      </k-view>
    </div>
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  extends: ModelView,
  prevnext: true,
  computed: {
    id() {
      return this.$api.users.url(this.model.id);
    },
    uploadApi() {
      return this.$urls.api + "/" + this.id + "/avatar";
    }
  },
  methods: {
    async deleteAvatar() {
      await this.$api.users.deleteAvatar(this.model.id)
      this.avatar = null;
      this.$store.dispatch("notification/success", ":)");
      this.$reload();
    },
    uploadedAvatar() {
      this.$store.dispatch("notification/success", ":)");
      this.$reload();
    }
  }
};
</script>

<style>
.k-user-profile {
  background: var(--color-white);
}
.k-user-profile > .k-view {
  padding-block: 3rem;
  display: flex;
  align-items: center;
  line-height: 0;
}
.k-user-profile .k-button-group {
  overflow: hidden;
  margin-inline-start: .75rem
}
.k-user-profile .k-button-group .k-button {
  display: block;
  padding-block: .25rem;
  overflow: hidden;
  white-space: nowrap;
}
.k-user-profile .k-button-group .k-button[disabled] {
  opacity: 1;
}

.k-user-profile .k-dropdown-content {
  margin-block-start: .5rem;
  inset-inline-start: 50%;
  transform: translateX(-50%);
}
.k-user-view-image .k-image {
  display: block;
  width: 4rem;
  height: 4rem;
  line-height: 0;
}
.k-user-view-image .k-button-text {
  opacity: 1;
}
.k-user-view-image .k-icon {
  width: 4rem;
  height: 4rem;
  background: var(--color-gray-900);
  color: var(--color-gray-500);
}
.k-user-name-placeholder {
  color: var(--color-gray-500);
  transition: color .3s;
}
.k-header[data-editable] .k-user-name-placeholder:hover {
  color: var(--color-gray-900);
}
</style>
