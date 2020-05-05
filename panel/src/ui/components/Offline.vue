<template>
  <div v-if="offline" class="k-offline-warning fixed flex items-center justify-center bg-backdrop">
    <p class="rounded-sm shadow-lg bg-orange-light px-3 py-2 flex items-center text-sm">
      <k-icon class="mr-2" type="bolt" /> {{ $t('offline') }}
    </p>
  </div>
</template>

<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      offline: false
    };
  },
  created() {
    this.$events.$on("offline", this.isOffline);
    this.$events.$on("online", this.isOnline);
  },
  methods: {
    isOnline() {
      this.offline = false;
    },
    isOffline() {
      if (this.disabled === false) {
        this.offline = true;
      }
    }
  }
};
</script>

<style lang="scss">
.k-offline-warning {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: z-index(loader);
}
</style>
