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
        :license="$system.license"
        :areas="$areas"
        :view="$view"
        @register="$refs.registration.open()"
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

    <!-- Registration dialog -->
    <k-registration ref="registration" @success="$reload" />

    <!-- Form buttons -->
    <k-form-buttons />

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
    <div v-if="offline" class="k-offline-warning">
      <p>The Panel is currently offline</p>
    </div>
  </div>
</template>

<script>
import search from "@/config/search.js"
import Registration from "@/components/Dialogs/RegistrationDialog.vue";

export default {
  components: {
    "k-registration": Registration
  },
  inheritAttrs: false,
  data() {
    return {
      offline: false,
      dragging: false
    };
  },
  computed: {
    fatal() {
      return this.$store.state.fatal;
    },
    searchTypes() {
      return search(this);
    },
    translation() {
      return this.$language ? this.$language.code : null;
    },
    defaultTranslation() {
      return this.$language ? this.$language.default : false;
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
      if (this.$system.isLocal === false) {
        this.offline = true;
      }
    }
  }
};
</script>

<style>
@import url('../../variables.css');

:root {

  /** Colors **/
  --color-background: var(--color-light);
  --color-border: var(--color-gray-400);
  --color-focus: var(--color-blue-600);
  --color-focus-light: var(--color-blue-400);
  --color-focus-outline: rgba(113, 143, 183, .25);
  --color-negative: var(--color-red-600);
  --color-negative-light: var(--color-red-400);
  --color-negative-outline: rgba(212, 110, 110, .25);
  --color-notice: var(--color-orange-600);
  --color-notice-light: var(--color-orange-400);
  --color-positive: var(--color-green-600);
  --color-positive-light: var(--color-green-400);
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

  --bg-pattern: repeating-conic-gradient(rgba(0,0,0, 0) 0% 25%, rgba(0,0,0, .2) 0% 50%) 50% / 20px 20px;

  --shadow-sticky: rgba(0, 0, 0, .05) 0 2px 5px;
  --shadow-dropdown: var(--shadow-lg);
  --shadow-item: var(--shadow);

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
.k-panel-inside .k-panel-view {
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
