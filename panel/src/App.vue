<template>
  <div
    v-if="!$store.state.system.info.isBroken"
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-route="$route.name"
    :data-topbar="inside"
    :data-translation="translation"
    :data-translation-default="defaultTranslation"
    class="k-panel"
    tabindex="0"
  >
    <!-- Icons -->
    <keep-alive>
      <k-icons />
    </keep-alive>

    <!-- Header -->
    <header v-if="inside" class="k-panel-header">
      <k-topbar
        @register="$refs.registration.open()"
        @search="$refs.search.open();"
      />
    </header>

    <!-- Main view -->
    <main class="k-panel-view">
      <router-view />
    </main>

    <!-- Form buttons -->
    <k-form-buttons v-if="inside" />

    <!-- Search dialog -->
    <k-search
      v-if="inside"
      ref="search"
      :type="searchType"
      :types="searchTypes"
    />

    <!-- Error dialog -->
    <k-error-dialog />

    <!-- Fatal iframe -->
    <template v-if="fatal">
      <div class="k-fatal">
        <div class="k-fatal-box">
          <k-headline>The JSON response of the API could not be parsed:</k-headline>
          <iframe ref="fatal" class="k-fatal-iframe" />
        </div>
      </div>
    </template>

    <!-- Offline warning -->
    <div
      v-if="offline"
      class="k-offline-warning"
    >
      <p>The Panel is currently offline</p>
    </div>

    <!-- Registration dialog -->
    <k-registration v-if="inside" ref="registration" />
  </div>
  <div v-else class="k-panel">
    <main class="k-panel-view">
      <k-error-view>
        <p v-if="debug">
          {{ $store.state.system.info.error }}
        </p>
        <p v-else>
          The Panel cannot connect to the API
        </p>
      </k-error-view>
    </main>
  </div>
</template>

<script>
import Icons from "@/components/Misc/Icons.vue";
import Registration from "@/components/Dialogs/RegistrationDialog.vue";
import config from "@/config/config.js";
import search from "@/config/search.js"

export default {
  name: "App",
  components: {
    "k-icons": Icons,
    "k-registration": Registration,
  },
  data() {
    return {
      offline: false,
      dragging: false,
      debug: config.debug
    };
  },
  computed: {
    inside() {
      return !this.$route.meta.outside && this.$store.state.user.current
        ? true
        : false;
    },
    defaultTranslation() {
      return this.$store.state.languages.current ? this.$store.state.languages.current === this.$store.state.languages.default : false;
    },
    fatal() {
      return this.$store.state.fatal;
    },
    searchType() {
      return this.$store.state.view === 'users' ? 'users' : 'pages';
    },
    searchTypes() {
      return search(this);
    },
    translation() {
      return this.$store.state.languages.current ? this.$store.state.languages.current.code : false;
    }
  },
  watch: {
    fatal(html) {
      if (html !== null) {
        this.$nextTick(() => {
          try {
            let doc = this.$refs.fatal.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
          } catch (e) {
            console.error(e);
          }
        })
      }
    }
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
      if (this.$store.state.system.info.isLocal === false) {
        this.offline = true;
      }
    }
  }
};
</script>

<style>
@import url('./variables.css');

:root {

  /** Colors **/
  --color-background: var(--color-light);
  --color-border: var(--color-gray-400);
  --color-focus: var(--color-blue-600);
  --color-focus-light: var(--color-blue-500);
  --color-focus-outline: rgba(113, 143, 183, .25);
  --color-negative: var(--color-red-700);
  --color-negative-light: var(--color-red-500);
  --color-negative-outline: rgba(212, 110, 110, .25);
  --color-notice: var(--color-orange-600);
  --color-notice-light: var(--color-orange-500);
  --color-positive: var(--color-green-600);
  --color-positive-light: var(--color-green-500);
  --color-positive-outline: rgba(128, 149, 65, .25);
  --color-text: var(--color-gray-900);
  --color-text-light: var(--color-gray-600);

  --z-loader: 1000;
  --z-notification: 900;
  --z-dropdown: 800;
  --z-dialog: 700;
  --z-drawer: 600;
  --z-dropzone: 500;
  --z-toolbar: 400;
  --z-navigation: 300;
  --z-content: 200;
  --z-background: 100;

  --bg-pattern: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXR0ZXJuIGlkPSJhIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiPjxwYXRoIGZpbGw9InJnYmEoMCwgMCwgMCwgMC4yKSIgZD0iTTAgMGgxMHYxMEgwem0xMCAxMGgxMHYxMEgxMHoiLz48L3BhdHRlcm4+PHJlY3QgZmlsbD0idXJsKCNhKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==");

  --shadow-sticky: rgba(0, 0, 0, .05) 0 2px 5px;
  --shadow-dropdown: var(--shadow-lg);
  --shadow-item: var(--shadow);
  --shadow-focus:var(--shadow-focus);

  --field-input-padding: .5rem;
  --field-input-height: 2.25rem;
  --field-input-line-height: 1.25rem;
  --field-input-font-size: var(--text-base);
  --field-input-color-before: var(--color-gray-700);
  --field-input-color-after: var(--color-gray-700);
  --field-input-border: 1px solid var(--color-border);
  --field-input-focus-border: 1px solid var(--color-focus);
  --field-input-focus-outline: 2px solid var(--color-focus-outline);
  --field-input-invalid-border: 1px solid var(--color-negative-outline);
  --field-input-invalid-outline: 0;
  --field-input-invalid-focus-border: 1px solid var(--color-negative);
  --field-input-invalid-focus-outline: 2px solid var(--color-negative-outline);
  --field-input-background: var(--color-white);
  --field-input-disabled-color: var(--color-gray-500);
  --field-input-disabled-background: var(--color-white);
  --field-input-disabled-border: 1px solid var(--color-gray-300);
}

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
  overflow: hidden;
  height: 100%;
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity .5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}

.k-panel {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: var(--color-background);
}
.k-panel:focus {
  outline: 0;
}
.k-panel[data-loading] {
  animation: LoadingCursor .5s;
}
.k-panel-header {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: var(--z-navigation);
}
.k-panel .k-form-buttons {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: var(--z-navigation);
}
.k-panel-view {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  padding-bottom: 6rem;
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
  transform: translate3d(0, 0, 0);
}
.k-panel[data-topbar] .k-panel-view {
  top: 2.5rem;
}
.k-panel[data-loading]::after,
.k-panel[data-dragging] {
  user-select: none;
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

@keyframes LoadingCursor {
  100% {
    cursor: progress;
  }
}

@keyframes Spin {
  100% {
    transform: rotate(360deg);
  }
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

.k-icons {
  position: absolute;
  width: 0;
  height: 0;
}

.k-fatal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--color-backdrop);
  display: flex;
  z-index: var(--z-dialog);
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}
.k-fatal-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #fff;
  padding: .75rem 1.5rem 1.5rem;
  box-shadow: var(--shadow-xl);
  border-radius: var(--rounded);
}
.k-fatal-box .k-headline {
  margin-bottom: .75rem;
}
.k-fatal-iframe {
  border: 0;
  width: 100%;
  flex-grow: 1;
  border: 2px solid var(--color-border);
}
</style>
