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
          <k-registration-buttons v-if="!registered" />
          <k-form-indicator v-bind="indicator" />
          <k-languages-dropdown v-if="languages" />
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
  </div>
</template>

<script>
import search from "@/app/plugins/search.js";
import views from "@/app/plugins/views.js";

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
    languages: {
      Boolean
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
    loading() {
      return this.$store.state.isLoading;
    },
    menu() {
      let menu = this.$helper.clone(this.views);

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
    registered() {
      return this.$store.state.system.license;
    },
    searchTypes() {
      return search(this);
    },
    views() {
      return views(this).filter(view => {
        if (typeof view.menu === 'function') {
          return view.menu(this) !== false;
        }

        return view.menu !== false;
      });
    },
    viewCrumb() {
      let crumb = this.views.find(view => view.id === this.view) || this.views[0];

      // inject the loading state
      crumb.loading = this.loading;

      return crumb;
    }
  },
  methods: {
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
