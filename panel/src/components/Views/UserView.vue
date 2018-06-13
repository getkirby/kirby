<template>
  <kirby-error-view v-if="issue">
    {{ issue.message }}
  </kirby-error-view>
  <kirby-view v-else-if="ready" class="kirby-user-view">
    <kirby-header
      :editable="permissions.changeName"
      :tabs="tabs"
      :tab="tab"
      @edit="action('rename')"
    >
      <kirby-button class="kirby-user-view-image" @click="$refs.upload.open()">
        <kirby-image
          v-if="avatar"
          :cover="true"
          :src="avatar"
          ratio="1/1"
        />
        <kirby-icon v-else type="user" />
      </kirby-button>

      {{ user.name }}

      <kirby-button-group slot="left">
        <template v-if="avatar">
          <kirby-dropdown>
            <kirby-button icon="image" @click="$refs.picture.toggle()">
              {{ $t('user.avatar') }}
            </kirby-button>
            <kirby-dropdown-content ref="picture">
              <kirby-dropdown-item icon="upload" @click="$refs.upload.open()">
                {{ $t('change') }}
              </kirby-dropdown-item>
              <kirby-dropdown-item icon="trash" @click="action('picture.delete')">
                {{ $t('delete') }}
              </kirby-dropdown-item>
            </kirby-dropdown-content>
          </kirby-dropdown>
        </template>
        <template v-else>
          <kirby-button icon="image" @click="$refs.upload.open()">
            {{ $t('user.avatar') }}
          </kirby-button>
        </template>
        <kirby-dropdown>
          <kirby-button icon="cog" @click="$refs.settings.toggle()">
            {{ $t('settings') }}
          </kirby-button>
          <kirby-dropdown-content ref="settings" :options="options" @action="action" />
        </kirby-dropdown>
      </kirby-button-group>
      <kirby-button-group v-if="user.id && $route.name === 'User'" slot="right">
        <kirby-button :disabled="!prev" v-bind="prev" icon="angle-left" />
        <kirby-button :disabled="!next" v-bind="next" icon="angle-right" />
      </kirby-button-group>
    </kirby-header>

    <kirby-tabs
      v-if="user && tabs.length"
      ref="tabs"
      :key="'user-' + user.id + '-tabs'"
      :parent="'users/' + user.id"
      :tabs="tabs"
      @tab="tab = $event"
    />

    <kirby-box v-else-if="ready" :text="$t('user.blueprint', { role: user.role.name })" />

    <kirby-user-role-dialog ref="role" @success="fetch" />
    <kirby-user-rename-dialog ref="rename" @success="fetch" />
    <kirby-user-password-dialog ref="password" />
    <kirby-user-language-dialog ref="language" @success="fetch" />
    <kirby-user-remove-dialog ref="remove" />

    <kirby-upload
      ref="upload"
      :url="uploadApi"
      :multiple="false"
      accept="image/jpeg"
      @success="uploadedAvatar"
    />
  </kirby-view>

</template>

<script>
import PrevNext from "@/mixins/prevnext.js";
import config from "@/config/config.js";

export default {
  mixins: [PrevNext],
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      tab: null,
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
        changeName: false
      },
      issue: null,
      avatar: null,
      options: null
    };
  },
  computed: {
    prev() {
      if (this.user.prev) {
        return {
          link: this.$api.user.link(this.user.prev.id),
          tooltip: this.user.prev.name
        };
      }
    },
    next() {
      if (this.user.next) {
        return {
          link: this.$api.user.link(this.user.next.id),
          tooltip: this.user.next.name
        };
      }
    },
    uploadApi() {
      return config.api + "/users/" + this.user.id + "/avatar";
    }
  },
  methods: {
    action(action) {
      switch (action) {
        case "picture.delete":
          this.$api.user.deleteAvatar(this.id).then(() => {
            this.$store.dispatch(
              "notification/success",
              this.$t("user.avatar.deleted")
            );
            this.avatar = null;
          });
          break;
        case "role":
          this.$refs.role.open(this.user.id);
          break;
        case "password":
          this.$refs.password.open(this.user.id);
          break;
        case "language":
          this.$refs.language.open(this.user.id);
          break;
        case "rename":
          this.$refs.rename.open(this.user.id);
          break;
        case "remove":
          this.$refs.remove.open(this.user.id);
          break;
      }
    },
    fetch() {
      this.$api.user
        .get(this.id, { view: "panel" })
        .then(user => {
          this.user = user;
          this.tabs = user.blueprint.tabs;
          this.ready = true;
          this.permissions = user.blueprint.options;
          this.options = ready => {
            this.$api.user.options(this.user.id).then(options => {
              ready(options);
            });
          };

          if (user.avatar.exists) {
            this.avatar = user.avatar.url + "?v=" + user.avatar.modified;
          } else {
            this.avatar = null;
          }

          if (this.$route.name === "User") {
            this.$store.dispatch("breadcrumb", this.$api.user.breadcrumb(user));
          } else {
            this.$store.dispatch("breadcrumb", []);
          }

          this.$store.dispatch("title", this.user.name);
        })
        .catch(error => {
          this.issue = error;
        });
    },
    uploadedAvatar() {
      this.$store.dispatch(
        "notification/success",
        this.$t("user.avatar.uploaded")
      );
      this.fetch();
    }
  }
};
</script>

<style lang="scss">
.kirby-user-view .kirby-header .kirby-headline {
  position: relative;
  padding-left: 1.5em;
}
.kirby-user-view-image {
  position: absolute;
  width: 2rem;
  height: 2rem;
  top: 50%;
  left: -0.375rem;
  margin-top: -1.375rem;
  border-radius: 50%;
  overflow: hidden;
}
.kirby-user-view-image .kirby-image {
  width: 2rem;
  height: 2rem;
  overflow: hidden;
  line-height: 0;
  background: red;
}
.kirby-user-view-image .kirby-icon {
  width: 2em;
  height: 2em;
  background: $color-dark;
  border-radius: 50%;
  color: $color-light-grey;
}
</style>
