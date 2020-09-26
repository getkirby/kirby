<template>
  <portal v-if="isOpen">
    <div
      :data-dimmed="dimmed"
      :data-loading="loading"
      class="k-overlay"
      v-on="$listeners"
    >
      <k-loader
        v-if="loading"
      />
      <slot
        v-else
        :close="close"
        :isOpen="isOpen"
      />
    </div>
  </portal>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    dimmed: {
      type: Boolean,
      default: true
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isOpen: false,
      scrollTop: 0
    };
  },
  created() {
    this.$events.$on("keydown.esc", this.close, false);
  },
  destroyed() {
    this.$events.$off("keydown.esc", this.close, false);
  },
  methods: {
    storeScrollPosition() {
      const view = document.querySelector(".k-panel-view");

      if (view && view.scrollTop) {
        this.scrollTop = view.scrollTop;
      } else {
        this.scrollTop = 0;
      }
    },
    restoreScrollPosition() {
      const view = document.querySelector(".k-panel-view");

      if (view && view.scrollTop) {
        view.scrollTop = this.scrollTop;
      }
    },
    open() {
      this.storeScrollPosition();
      this.isOpen = true;
      this.$emit("open");
      this.$events.$on("keydown.esc", this.close);
    },
    close() {
      this.isOpen = false;
      this.$emit("close");
      this.$events.$off("keydown.esc", this.close);
      this.restoreScrollPosition();
    }
  }
};
</script>

<style lang="scss">
.k-overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: z-index(dialog);
  transform: translate3d(0, 0, 0);
}
.k-overlay[data-loading] {
  color: $color-white;
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-overlay[data-dimmed] {
  background: $color-backdrop;
}
</style>
