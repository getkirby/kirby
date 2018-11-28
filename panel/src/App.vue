<template>
  <div
    v-if="!$store.state.system.info.isBroken"
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-topbar="!$route.meta.outside"
    class="k-panel"
  >
    <div class="k-panel-header" v-if="inside">
      <k-topbar @register="$refs.registration.open()" />
      <k-search v-if="$store.state.search" v-bind="$store.state.search" />
    </div>
    <main class="k-panel-view">
      <router-view />
    </main>
    <k-form-buttons v-if="inside" />
    <k-error-dialog />
    <div v-if="offline" class="k-offline-warning">
      <p>The panel is currently offline</p>
    </div>
    <k-registration ref="registration" />
  </div>
  <div v-else class="k-panel">
    <main class="k-panel-view">
      <k-error-view>
        The panel cannot connect to the API 😭
      </k-error-view>
    </main>
  </div>
</template>

<script>
import Search from "@/components/Navigation/Search.vue";
import Registration from "@/components/Dialogs/RegistrationDialog.vue";

export default {
  name: "App",
  components: {
    "k-registration": Registration,
    "k-search": Search
  },
  data() {
    return {
      offline: false,
      dragging: false
    };
  },
  computed: {
    inside() {
      return !this.$route.meta.outside && this.$store.state.user.current;
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
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
}
.k-panel[data-topbar] .k-panel-view {
  top: 2.5rem;
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

.k-offscreen {
  clip-path: inset(100%);
  clip: rect(1px, 1px, 1px, 1px);
  height: 1px;
  overflow: hidden;
  position: absolute;
  white-space: nowrap;
  width: 1px;
}

</style>
