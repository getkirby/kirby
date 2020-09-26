<template>
  <portal v-if="isOpen">
    <div
      ref="overlay"
      :data-centered="loading || centered"
      :data-dimmed="dimmed"
      :data-loading="loading"
      class="k-overlay"
      v-on="$listeners"
      @mousedown="close"
    >
      <k-loader
        v-if="loading"
        class="k-overlay-loader"
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
    centered: {
      type: Boolean,
      default: false
    },
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

      // prevent that clicks on the overlay slot trigger close
      setTimeout(() => {
        document.querySelector(".k-overlay > *").addEventListener("mousedown", e => e.stopPropagation());
      }, 10)
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
.k-overlay[data-centered] {
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-overlay[data-dimmed] {
  background: $color-backdrop;
}
.k-overlay-loader {
  color: $color-white;
}
</style>
