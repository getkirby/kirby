<template>
  <div :data-loading="$store.state.isLoading" class="kirby-panel">
    <kirby-topbar />
    <kirby-search v-if="$store.state.search" v-bind="$store.state.search" />
    <main class="kirby-panel-view">
      <router-view />
    </main>
    <kirby-license-bar />
    <kirby-error-dialog />
    <div v-if="offline" class="kirby-offline-warning">
      <p>The panel is currently offline</p>
    </div>
  </div>
</template>

<script>
import LicenseBar from "@/components/Layout/LicenseBar.vue";
import Search from "@/components/Navigation/Search.vue";

export default {
  name: "App",
  components: {
    "kirby-license-bar": LicenseBar,
    "kirby-search": Search
  },
  data() {
    return {
      offline: false
    };
  },
  created() {
    this.$events.$on("offline", this.isOffline);
    this.$events.$on("online", this.isOnline);
    this.$events.$on('key.cmd+f', this.search);
  },
  destroyed() {
    this.$events.$off("offline", this.isOffline);
    this.$events.$off("online", this.isOnline);
    this.$events.$off('key.cmd+f', this.search);
  },
  methods: {
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

html {
  font-family: "Roboto", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
    Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji",
    "Segoe UI Symbol";
  background: #efefef;
  overflow: hidden;
  height: 100%;
}

html,
body {
  color: $color-dark;
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

.kirby-panel {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}
.kirby-panel-view {
  position: relative;
  flex-grow: 1;
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
}
.kirby-panel[data-loading] {
  animation: Loading 0.5s;
}
.kirby-panel[data-loading]::after,
.kirby-offline-warning {
  position: fixed;
  content: " ";
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: z-index(loader);
}
.kirby-offline-warning {
  background: rgba($color-dark, 0.7);
  content: "offline";
  display: flex;
  align-items: center;
  justify-content: center;
  color: $color-white;
}

@keyframes Loading {
  100% {
    cursor: progress;
  }
}
</style>
