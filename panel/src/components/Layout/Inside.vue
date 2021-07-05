<template>
  <div
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-translation="translation"
    :data-translation-default="defaultTranslation"
    :dir="$translation.direction"
    class="k-panel k-panel-inside"
    tabindex="0"
  >
    <!-- Header -->
    <header class="k-panel-header">
      <k-topbar
        :breadcrumb="$view.breadcrumb"
        :license="$license"
        :menu="$menu"
        :view="$view"
        @search="$refs.search.open();"
      />

      <k-search
        ref="search"
        :type="$view.search || 'pages'"
        :types="searchTypes"
      />
    </header>

    <!-- Main view -->
    <main class="k-panel-view">
      <slot />
    </main>

    <!-- Form buttons -->
    <k-form-buttons :lock="lock" />

    <!-- Error dialog -->
    <k-error-dialog />

    <!-- Fiber dialogs -->
    <template v-if="$store.state.dialog && $store.state.dialog.props">
      <k-fiber-dialog v-bind="dialog" />
    </template>

    <!-- Fatal iframe -->
    <k-fatal v-if="$store.state.fatal" />

    <!-- Offline warning -->
    <div v-if="offline" class="k-offline-warning">
      <p>The Panel is currently offline</p>
    </div>

    <!-- Icons -->
    <k-icons />
  </div>
</template>

<script>
import search from "@/config/search.js";

export default {
  inheritAttrs: false,
  props: {
    lock: [Boolean, Object],
  },
  data() {
    return {
      offline: false
    }
  },
  computed: {
    defaultTranslation() {
      return this.$language ? this.$language.default : false;
    },
    dialog() {
      return this.$helper.clone(this.$store.state.dialog);
    },
    searchTypes() {
      return search(this);
    },
    translation() {
      return this.$language ? this.$language.code : null;
    },
  },
  created() {
    this.$events.$on("offline", this.isOffline);
    this.$events.$on("online", this.isOnline);
    this.$events.$on("drop", this.drop);
  },
  destroyed() {
    this.$events.$off("offline", this.isOffline);
    this.$events.$off("online", this.isOnline);
    this.$events.$off("drop", this.drop);
  },
  methods: {
    drop() {
      // remove any drop data from the store
      this.$store.dispatch("drag", null);
    },
    isOnline() {
      this.offline = false;
    },
    isOffline() {
      if (this.$system.isLocal === false) {
        this.offline = true;
      }
    }
  }
};
</script>

<style>
@import url('../../variables.css');

*,
*::before,
*::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

noscript {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  text-align: center;
}

html {
  font-family: var(--font-sans);
  background: var(--color-background);
}

html,
body {
  color: var(--color-gray-900);
  min-height: 100vh;
}

a {
  color: inherit;
  text-decoration: none;
}

li {
  list-style: none;
}

strong,
b {
  font-weight: var(--font-bold);
}

.k-panel-view {
  padding-top: 2.5rem;
  padding-bottom: 6rem;
}
.k-panel-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: var(--z-navigation);
}

.k-offline-warning {
  position: fixed;
  content: " ";
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: var(--z-loader);
  background: rgba(17, 17, 17, .7);
  content: "offline";
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-white);
}
.k-offscreen {
  clip-path: inset(100%);
  clip: rect(1px, 1px, 1px, 1px);
  height: 1px;
  overflow: hidden;
  position: absolute;
  white-space: nowrap;
  width: 1px;
}

.k-panel[data-loading] {
  animation: LoadingCursor .5s;
}
.k-panel[data-loading]::after,
.k-panel[data-dragging] {
  user-select: none;
}
@keyframes LoadingCursor {
  100% { cursor: progress; }
}
@keyframes Spin {
  100% { transform: rotate(360deg); }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity .5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
