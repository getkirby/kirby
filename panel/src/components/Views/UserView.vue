<template>
  <k-inside>
    <div v-if="ready" :data-locked="isLocked" class="k-user-view">
      <div class="k-user-profile">
        <k-view>
          <template v-if="user.avatar">
            <k-dropdown>
              <k-button
                :tooltip="$t('avatar')"
                :disabled="isLocked"
                class="k-user-view-image"
                @click="$refs.picture.toggle()"
              >
                <k-image
                  v-if="user.avatar"
                  :cover="true"
                  :src="user.avatar"
                  ratio="1/1"
                />
              </k-button>
              <k-dropdown-content ref="picture">
                <k-dropdown-item icon="upload" @click="$refs.upload.open()">
                  {{ $t('change') }}
                </k-dropdown-item>
                <k-dropdown-item icon="trash" @click="action('picture.delete')">
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
              @click="action('email')"
            >
              {{ $t("email") }}: {{ user.email }}
            </k-button>
            <k-button
              :disabled="!permissions.changeRole || isLocked"
              icon="bolt"
              @click="action('role')"
            >
              {{ $t("role") }}: {{ user.role }}
            </k-button>
            <k-button
              :disabled="!permissions.changeLanguage || isLocked"
              icon="globe"
              @click="action('language')"
            >
              {{ $t("language") }}: {{ user.language }}
            </k-button>
          </k-button-group>
        </k-view>
      </div>

      <k-view>
        <k-header
          :editable="permissions.changeName && !isLocked"
          :tab="tab"
          :tabs="tabs"
          @edit="action('rename')"
        >
          <span v-if="!user.name || user.name.length === 0" class="k-user-name-placeholder">{{ $t("name") }} …</span>
          <template v-else>
            {{ user.name }}
          </template>

          <template #left>
            <k-button-group>
              <k-dropdown>
                <k-button :disabled="isLocked" icon="cog" @click="$refs.settings.toggle()">
                  {{ $t('settings') }}
                </k-button>
                <k-dropdown-content ref="settings" :options="options" @action="action" />
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
          v-if="tab.columns"
          :blueprint="blueprint"
          :empty="$t('user.blueprint', { role: user.role.name })"
          :parent="'users/' + user.id"
          :tab="tab"
          :tabs="tabs"
        />

        <k-box
          v-else
          :text="$t('user.blueprint', { role: user.role.name })"
          theme="info"
        />

        <k-user-email-dialog ref="email" @success="$reload" />
        <k-user-language-dialog ref="language" @success="$reload" />
        <k-user-password-dialog ref="password" />
        <k-user-remove-dialog ref="remove" @success="$go('users')" />
        <k-user-rename-dialog ref="rename" @success="$reload" />
        <k-user-role-dialog ref="role" @success="$reload" />

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
import ModelView from "./ModelView";

export default {
  extends: ModelView,
  prevnext: true,
  props: {
    user: Object
  },
  computed: {
    options() {
      return async ready => {
        const options = await this.$api.users.options(this.user.id);
        ready(options);
      };
    },
    uploadApi() {
      return this.$urls.api + "/users/" + this.user.id + "/avatar";
    }
  },
  watch: {
    "user.id": {
      handler() {
        this.$store.dispatch("content/create", {
          id: "users/" + this.user.id,
          api: this.$api.users.link(this.user.id),
          content: this.user.content
        });
      },
      immediate: true
    }
  },
  methods: {
    async action(action) {
      switch (action) {
        case "email":
          this.$refs.email.open(this.user.id);
          break;
        case "language":
          this.$refs.language.open(this.user.id);
          break;
        case "password":
          this.$refs.password.open(this.user.id);
          break;
        case "picture.delete":
          await this.$api.users.deleteAvatar(this.id)
          this.avatar = null;
          this.$store.dispatch("notification/success", ":)");
          this.$reload();
          break;
        case "remove":
          this.$refs.remove.open(this.user.id);
          break;
        case "rename":
          this.$refs.rename.open(this.user.id);
          break;
        case "role":
          this.$refs.role.open(this.user.id);
          break;
        default:
          this.$store.dispatch("notification/error", "Not yet implemented");
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
  padding-top: 3rem;
  padding-bottom: 3rem;
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
  padding-top: .25rem;
  padding-bottom: .25rem;
  overflow: hidden;
  white-space: nowrap;
}
.k-user-profile .k-button-group .k-button[disabled] {
  opacity: 1;
}

.k-user-profile .k-dropdown-content {
  margin-top: .5rem;
  left: 50%;
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
