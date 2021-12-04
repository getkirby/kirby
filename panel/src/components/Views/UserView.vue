<template>
  <k-inside>
    <div
      :data-locked="isLocked"
      :data-id="model.id"
      :data-template="blueprint"
      class="k-user-view"
    >
      <div class="k-user-profile">
        <k-view>
          <k-dropdown>
            <k-button
              :tooltip="$t('avatar')"
              :disabled="isLocked"
              class="k-user-view-image"
              @click="onAvatar"
            >
              <k-image
                v-if="model.avatar"
                :cover="true"
                :src="model.avatar"
                ratio="1/1"
              />
              <k-icon v-else back="gray-900" color="gray-200" type="user" />
            </k-button>
            <k-dropdown-content
              v-if="model.avatar"
              ref="picture"
              :options="avatarOptions"
            />
          </k-dropdown>
          <k-button-group :buttons="buttons" />
        </k-view>
      </div>
      <k-view>
        <k-header
          :editable="permissions.changeName && !isLocked"
          :tab="tab.name"
          :tabs="tabs"
          @edit="$dialog(id + '/changeName')"
        >
          <span
            v-if="!model.name || model.name.length === 0"
            class="k-user-name-placeholder"
          >
            {{ $t("name") }} …
          </span>
          <template v-else>
            {{ model.name }}
          </template>

          <template #left>
            <k-button-group>
              <k-dropdown class="k-user-view-options">
                <k-button
                  :disabled="isLocked"
                  :text="$t('settings')"
                  icon="cog"
                  @click="$refs.settings.toggle()"
                />
                <k-dropdown-content ref="settings" :options="$dropdown(id)" />
              </k-dropdown>
              <k-languages-dropdown />
            </k-button-group>
          </template>
          <template #right>
            <k-prev-next v-if="!model.account" :prev="prev" :next="next" />
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
    <template #footer>
      <k-form-buttons :lock="lock" />
    </template>
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  extends: ModelView,
  computed: {
    avatarOptions() {
      return [
        {
          icon: "upload",
          text: this.$t("change"),
          click: () => this.$refs.upload.open()
        },
        {
          icon: "trash",
          text: this.$t("delete"),
          click: this.deleteAvatar
        }
      ];
    },
    buttons() {
      return [
        {
          icon: "email",
          text: `${this.$t("email")}: ${this.model.email}`,
          disabled: !this.permissions.changeEmail || this.isLocked,
          click: () => this.$dialog(this.id + "/changeEmail")
        },
        {
          icon: "bolt",
          text: `${this.$t("role")}: ${this.model.role}`,
          disabled: !this.permissions.changeRole || this.isLocked,
          click: () => this.$dialog(this.id + "/changeRole")
        },
        {
          icon: "globe",
          text: `${this.$t("language")}: ${this.model.language}`,
          disabled: !this.permissions.changeLanguage || this.isLocked,
          click: () => this.$dialog(this.id + "/changeLanguage")
        }
      ];
    },
    uploadApi() {
      return this.$urls.api + "/" + this.id + "/avatar";
    }
  },
  methods: {
    async deleteAvatar() {
      await this.$api.users.deleteAvatar(this.model.id);
      this.avatar = null;
      this.$store.dispatch("notification/success", ":)");
      this.$reload();
    },
    onAvatar() {
      if (this.model.avatar) {
        this.$refs.picture.toggle();
      } else {
        this.$refs.upload.open();
      }
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
  margin-inline-start: 0.75rem;
}
.k-user-profile .k-button-group .k-button {
  display: block;
  padding-block: 0.25rem;
  overflow: hidden;
  white-space: nowrap;
}

.k-user-view-image .k-image,
.k-user-view-image .k-icon {
  width: 5rem;
  height: 5rem;
  line-height: 0;
}
.k-user-view-image[data-disabled="true"] {
  opacity: 1;
}
.k-user-view-image .k-image {
  display: block;
}
.k-user-view-image .k-button-text {
  opacity: 1;
}

.k-user-name-placeholder {
  color: var(--color-gray-500);
  transition: color 0.3s;
}
.k-header[data-editable="true"] .k-user-name-placeholder:hover {
  color: var(--color-gray-900);
}
</style>
