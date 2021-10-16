<template>
  <div
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-language="language"
    :data-language-default="defaultLanguage"
    :data-role="role"
    :data-translation="$translation.code"
    :data-user="user"
    :dir="$translation.direction"
    class="k-panel"
  >
    <slot />

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
export default {
  data() {
    return {
      offline: false
    };
  },
  computed: {
    defaultLanguage() {
      return this.$language ? this.$language.default : false;
    },
    dialog() {
      return this.$helper.clone(this.$store.state.dialog);
    },
    language() {
      return this.$language ? this.$language.code : null;
    },
    role() {
      return this.$user ? this.$user.role : null;
    },
    user() {
      return this.$user ? this.$user.id : null;
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
.k-offline-warning {
  position: fixed;
  content: " ";
  inset: 0;
  z-index: var(--z-loader);
  background: rgba(17, 17, 17, 0.7);
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
  animation: LoadingCursor 0.5s;
}
.k-panel[data-loading]::after,
.k-panel[data-dragging] {
  user-select: none;
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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
