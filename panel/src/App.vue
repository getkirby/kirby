<template>
  <div
    v-if="!$store.state.system.info.isBroken"
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-topbar="inside"
    :data-dialog="$store.state.dialog"
    class="k-panel"
  >
    <header v-if="inside" class="k-panel-header">
      <k-topbar @register="$refs.registration.open()" />
      <k-search v-if="$store.state.search" v-bind="$store.state.search" />
    </header>
    <main class="k-panel-view">
      <router-view />
    </main>
    <k-form-buttons v-if="inside" />
    <k-error-dialog />
    <div v-if="offline" class="k-offline-warning">
      <p>The panel is currently offline</p>
    </div>
    <k-registration v-if="inside" ref="registration" />
  </div>
  <div v-else class="k-panel">
    <main class="k-panel-view">
      <k-error-view>
        <p v-if="debug">{{ $store.state.system.info.error }}</p>
        <p v-else>The panel cannot connect to the API</p>
      </k-error-view>
    </main>
  </div>
</template>

<script>
import Registration from "@/components/Dialogs/RegistrationDialog.vue";
import config from "@/config/config.js";

export default {
  name: "App",
  components: {
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
    }
  },
  created() {
    this.$events.$on("offline", this.isOffline);
    this.$events.$on("online", this.isOnline);
    this.$events.$on("keydown.cmd.shift.f", this.search);
    this.$events.$on("drop", this.drop);
  },
  destroyed() {
    this.$events.$off("offline", this.isOffline);
    this.$events.$off("online", this.isOnline);
    this.$events.$off("keydown.cmd.shift.f", this.search);
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
    },
    search(event) {
      event.preventDefault();
      this.$store.dispatch("search", true);
    }
  }
};
</script>

<style lang="scss">
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
  font-family: $font-family-sans;
  background: $color-background;
}

html,
body {
  color: $color-dark;
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
  font-weight: $font-weight-bold;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
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
  background: $color-background;
}
.k-panel[data-loading] {
  animation: LoadingCursor 0.5s;
}
.k-panel-header {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: z-index(navigation);
}
.k-panel .k-form-buttons {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: z-index(navigation);
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
.k-panel[data-dialog] .k-panel-view {
  overflow: hidden;
  transform: none;
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
  z-index: z-index(loader);
}
.k-offline-warning {
  background: rgba($color-dark, 0.7);
  content: "offline";
  display: flex;
  align-items: center;
  justify-content: center;
  color: $color-white;
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
</style>
