<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>

  <div v-else-if="ready" :data-locked="isLocked" class="k-user-view">
    <div class="k-user-profile">
      <k-view>
        <template v-if="avatar">
          <k-dropdown>
            <k-button
              :tooltip="$t('avatar')"
              :disabled="isLocked"
              class="k-user-view-image"
              @click="$refs.picture.toggle()"
            >
              <k-image
                v-if="avatar"
                :cover="true"
                :src="avatar"
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
          <k-button :tooltip="$t('avatar')" class="k-user-view-image" @click="$refs.upload.open()">
            <k-icon type="user" />
          </k-button>
        </template>

        <k-button-group>
          <k-button :disabled="!permissions.changeEmail || isLocked" icon="email" @click="action('email')">
            {{ $t("email") }}: {{ user.email }}
          </k-button>
          <k-button :disabled="!permissions.changeRole || isLocked" icon="bolt" @click="action('role')">
            {{ $t("role") }}: {{ user.role.title }}
          </k-button>
          <k-button :disabled="!permissions.changeLanguage || isLocked" icon="globe" @click="action('language')">
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

        <k-button-group slot="left">
          <k-dropdown>
            <k-button :disabled="isLocked" icon="cog" @click="$refs.settings.toggle()">
              {{ $t('settings') }}
            </k-button>
            <k-dropdown-content ref="settings" :options="options" @action="action" />
          </k-dropdown>
          <k-languages-dropdown />
        </k-button-group>

        <k-prev-next
          v-if="user.id && $route.name === 'User'"
          slot="right"
          :prev="prev"
          :next="next"
        />
      </k-header>

      <k-sections
        v-if="user"
        :blueprint="user.blueprint.name"
        :empty="$t('user.blueprint', { role: user.role.name })"
        :parent="'users/' + user.id"
        :tab="tab"
        :tabs="tabs"
      />

      <k-user-email-dialog ref="email" @success="fetch" />
      <k-user-language-dialog ref="language" @success="fetch" />
      <k-user-password-dialog ref="password" />
      <k-user-remove-dialog ref="remove" />
      <k-user-rename-dialog ref="rename" @success="fetch" />
      <k-user-role-dialog ref="role" @success="fetch" />

      <k-upload
        ref="upload"
        :url="uploadApi"
        :multiple="false"
        accept="image/*"
        @success="uploadedAvatar"
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
    id: {
      type: [Boolean, String],
      required: true
    },
    tab: String
  },
  data() {
    return {
      tabs: [],
      ready: false,
      user: {
        role: {
          name: null
        },
        name: null,
        language: null,
        prev: null,
        next: null
      },
      permissions: {
        changeEmail: true,
        changeName: true,
        changeLanguage: true,
        changeRole: true
      },
      issue: null,
      avatar: null,
      options: null
    };
  },
  computed: {
    language() {
      return this.$store.state.languages.current;
    },
    next() {
      if (this.user.next) {
        return {
          link: this.$api.users.link(this.user.next.id),
          tooltip: this.user.next.name
        };
      }

      return null;
    },
    prev() {
      if (this.user.prev) {
        return {
          link: this.$api.users.link(this.user.prev.id),
          tooltip: this.user.prev.name
        };
      }

      return null;
    },
    uploadApi() {
      return config.api + "/users/" + this.user.id + "/avatar";
    }
  },
  watch: {
    "$route.name": {
      handler(name) {
        if (name === "Account") {
          this.$store.dispatch("breadcrumb", []);
        }
      },
      immediate: true
    },
    language() {
      this.fetch();
    },
    id() {
      this.fetch();
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
    async fetch() {
      // don't load a user if there's no id
      if (!this.id) {
        return;
      }

      try {
        this.user = await this.$api.users.get(this.id, { view: "panel" });
        this.tabs = this.user.blueprint.tabs;
        this.ready = true;
        this.permissions = this.user.options;
        this.options = async ready => {
          const options = await this.$api.users.options(this.user.id);
          ready(options);
        };

        if (this.user.avatar) {
          this.avatar = this.user.avatar.url;
        } else {
          this.avatar = null;
        }

        if (this.$route.name === "User") {
          this.$store.dispatch(
            "breadcrumb",
            this.$api.users.breadcrumb(this.user)
          );
        }

        this.$store.dispatch("title", this.user.name || this.user.email);
        this.$store.dispatch("content/create", {
          id: "users/" + this.user.id,
          api: this.$api.users.link(this.user.id),
          content: this.user.content
        });

      } catch (error) {
        window.console.error(error);
        this.issue = error;
      }
    },
    uploadedAvatar() {
      this.$store.dispatch("notification/success", ":)");
      this.fetch();
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
}
[dir="ltr"] .k-user-profile .k-button-group {
  margin-left: .75rem;
}

[dir="rtl"] .k-user-profile .k-button-group {
  margin-right: .75rem;
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
