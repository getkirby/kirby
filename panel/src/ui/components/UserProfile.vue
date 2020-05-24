<template>
  <div class="k-user-profile bg-white py-12">
    <k-view class="k-user-profile-layout items-center">
      <template>
        <k-dropdown>
          <k-button
            :tooltip="$t('avatar')"
            :disabled="!canChangeAvatar"
            class="k-user-profile-image"
            @click="$refs.picture.toggle()"
          >
            <k-item-figure v-bind="avatar" />
          </k-button>
          <k-dropdown-content
            ref="picture"
            :options="avatarOptions"
          />
        </k-dropdown>
      </template>

      <k-button-group>
        <k-button
          :disabled="!canChangeEmail"
          icon="email"
          @click="$emit('email')"
        >
          {{ $t("email") }}: {{ user.email || "–" }}
        </k-button>
        <k-button
          :disabled="!canChangeRole"
          icon="bolt"
          @click="$emit('role')"
        >
          {{ $t("role") }}: {{ user.role.title || "–" }}
        </k-button>
        <k-button
          :disabled="!canChangeLanguage"
          icon="globe"
          @click="$emit('language')"
        >
          {{ $t("language") }}: {{ user.language || "–" }}
        </k-button>
      </k-button-group>
    </k-view>
  </div>
</template>

<script>
export default {
  props: {
    canChangeAvatar: {
      type: Boolean,
      default: true
    },
    canChangeEmail: {
      type: Boolean,
      default: true
    },
    canChangeLanguage: {
      type: Boolean,
      default: true
    },
    canChangeRole: {
      type: Boolean,
      default: true
    },
    user: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {
    avatar() {
      return {
        preview: {
          image: this.user.avatar.url,
          icon: "user",
          back: "black",
          ratio: "1/1",
          color: "gray-light"
        }
      }
    },
    avatarOptions() {
      if (this.avatar.url) {
        return [
          {
            icon: "upload",
            text: this.$t("replace"),
            click: () => $emit("uploadAvatar")
          },
          {
            icon: "trash",
            text: this.$t("delete"),
            click: () => $emit("removeAvatar")
          }
        ];
      }

      return [
        {
          icon: "upload",
          text: this.$t("upload"),
          click: () => $emit("uploadAvatar")
        }
      ];
    }
  }
};
</script>

<style lang="scss">
/** Layout **/
.k-user-profile-layout {
  @media screen and (min-width: $breakpoint-sm) {
    display: flex;
  }
}

.k-user-profile-image {
  @media screen and (max-width: $breakpoint-sm) {
    margin-bottom: 1rem;
  }
  @media screen and (min-width: $breakpoint-sm) {
    margin-right: 1.5rem;
  }
}

.k-user-profile-image .k-item-figure {
  display: block;
  width: 5rem;
  height: 5rem;
  line-height: 0;
}
.k-user-profile-image .k-dropdown-content {
  margin-top: 2rem;
  left: 50%;
  transform: translateX(-50%);
}
.k-user-profile-image[data-disabled] {
  opacity: 1;
}
.k-user-profile .k-button-group .k-button {
  display: block;
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
  overflow: hidden;
  white-space: nowrap;
}
</style>
