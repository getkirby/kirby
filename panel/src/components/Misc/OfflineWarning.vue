<template>
  <div v-if="offline" class="k-offline-warning">
    <p><k-icon type="bolt" /> {{ $t("error.offline") }}</p>
  </div>
</template>

<script>
/**
 * @internal
 */
export default {
  data() {
    return {
      offline: false
    };
  },
  created() {
    this.$events.$on("offline", this.isOffline);
    this.$events.$on("online", this.isOnline);
  },
  destroyed() {
    this.$events.$off("offline", this.isOffline);
    this.$events.$off("online", this.isOnline);
  },
  methods: {
    isOnline() {
      this.offline = false;
    },
    isOffline() {
      this.offline = true;
    }
  }
};
</script>

<style>
.k-offline-warning {
  position: fixed;
  inset: 0;
  z-index: var(--z-offline);
  background: var(--color-backdrop);
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
.k-offline-warning p {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--color-white);
  box-shadow: var(--shadow);
  padding: 0.75rem;
  border-radius: var(--rounded);
}
.k-offline-warning p .k-icon {
  color: var(--color-red-400);
}
</style>
