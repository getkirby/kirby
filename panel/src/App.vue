<template>
  <div
    v-if="!$store.state.system.info.isBroken"
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    class="k-panel"
  >
    <k-topbar />
    <k-search v-if="$store.state.search" v-bind="$store.state.search" />
    <k-license-bar />
    <main class="k-panel-view">
      <router-view />
    </main>
    <k-form-buttons />
    <k-error-dialog />
    <div v-if="offline" class="k-offline-warning">
      <p>The panel is currently offline</p>
    </div>
  </div>

  <k-error-view v-else>
    The panel cannot connect to the API 😭
  </k-error-view>
</template>

<script>
import LicenseBar from "@/components/Layout/LicenseBar.vue";
import Search from "@/components/Navigation/Search.vue";

export default {
  name: "App",
  components: {
    "k-license-bar": LicenseBar,
    "k-search": Search
  },
  data() {
    return {
      offline: false,
      dragging: false
    };
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

html {
  font-family: $font-family-sans;
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

.k-panel {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}
.k-panel-view {
  position: relative;
  flex-grow: 1;
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
}
.k-panel[data-loading] {
  animation: Loading 0.5s;
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

@keyframes Loading {
  100% {
    cursor: progress;
  }
}
</style>
