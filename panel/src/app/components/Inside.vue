<template>
  <div
    :data-loading="loading"
    :data-translation="translation"
    :data-translation-default="defaultTranslation"
    class="k-panel k-panel-inside"
  >
    <!-- Topbar -->
    <header class="k-panel-header">
      <k-topbar
        :breadcrumb="[
          viewCrumb,
          ...breadcrumb
        ]"
        :menu="menu"
        :loading="loading"
        @search="onSearch"
      >
        <template v-slot:options>
          <k-registration-buttons
            v-if="registered === false"
            @register="onRegister"
          />
          <k-form-indicator v-bind="indicator" />
        </template>
      </k-topbar>
    </header>

    <!-- Main View -->
    <main class="k-panel-view">
      <k-notifications />
      <slot />
    </main>

    <!-- Search Dialog -->
    <k-search
      ref="search"
      :type="search"
      :types="searchTypes"
    />

    <!-- Registration Dialog -->
    <k-registration-dialog ref="registration" />
  </div>
</template>

<script>
export default {
  props: {
    breadcrumb: {
      type: Array,
      default() {
        return []
      }
    },
    defaultTranslation: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    registered: {
      type: Boolean,
      default: false
    },
    search: {
      type: String,
    },
    translation: {
      type: [Boolean, String],
      default: false
    },
    view: {
      type: String,
      default: "site"
    }
  },
  created() {
    this.$events.$on("keydown.cmd.shift.f", this.onSearch);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.shift.f", this.onSearch);
  },
  computed: {
    indicator() {
      return {
        models: this.$store.state.content.models,
        languages: this.$store.state.languages
      };
    },
    menu() {
      let menu = this.views;

      if (this.view) {
        menu = menu.map(item => {
          if (this.view === item.id) {
            item.current = true;
            item.color   = "blue";
          }

          return item;
        });
      }

      return menu;
    },
    searchTypes() {
      return {
        pages: {
          label: "Pages",
          icon: "page",
          search() {
            return async ({ query, limit }) => {
              return [];
            };
          }
        },
        files: {
          label: "Files",
          icon: "image",
          search(q) {
            return async () => {
              return [];
            }
          }
        },
        users: {
          label: "Users",
          icon: "users",
          search(q) {
            return async () => {
              return [];
            }
          }
        }
      };
    },
    views() {
      // TODO: replace with views from store
      return [
        {
          id: "site",
          link: "/site",
          icon: "home",
          text: this.$t("view.site"),
        },
        {
          id: "users",
          link: "/users",
          icon: "users",
          text: this.$t("view.users"),
        },
        {
          id: "settings",
          link: "/settings",
          icon: "settings",
          text: this.$t("view.settings")
        },
        "-",
        {
          id: "account",
          link: "/account",
          icon: "account",
          text: this.$t("view.account")
        },
        "-",
        {
          id: "logout",
          link: "/logout",
          icon: "logout",
          text: this.$t("logout")
        },
      ];
    },
    viewCrumb() {
      return this.views.find(view => view.id === this.view) || this.views[0];
    }
  },
  methods: {
    onRegister() {
      this.$refs.registration.open();
    },
    onSearch(event) {
      event.preventDefault();
      this.$refs.search.open();
    }
  }
};
</script>

<style lang="scss">
.k-panel-inside {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.k-panel-inside .k-panel-header {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: z-index(navigation);
}
.k-panel-inside .k-panel-view {
  position: absolute;
  top: 2.5rem;
  right: 0;
  bottom: 0;
  left: 0;
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
  transform: translate3d(0, 0, 0);
}
</style>
