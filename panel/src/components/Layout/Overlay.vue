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
    autofocus: {
      type: Boolean,
      default: true,
    },
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
  methods: {
    focus() {
      let target = this.$refs.overlay.querySelector(
        "[autofocus], [data-autofocus], input, textarea, select, button"
      );

      if (target && typeof target.focus === "function") {
        target.focus();
        return;
      }
    },
    focustrap(e) {
      if (this.$refs.overlay && this.$refs.overlay.contains(e.target) === false) {
        this.focus();
      }
    },
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

      // focus trap
      document.addEventListener("focus", this.focustrap, true);

      // esc
      this.$events.$on("keydown.esc", this.close, false);

      setTimeout(() => {
        // autofocus
        if (this.autofocus === true) {
          this.focus();
        }

        // prevent that clicks on the overlay slot trigger close
        document.querySelector(".k-overlay > *").addEventListener("mousedown", e => e.stopPropagation());
      }, 10)
    },
    close() {
      this.isOpen = false;
      this.$emit("close");
      this.$events.$off("keydown.esc", this.close);
      this.restoreScrollPosition();

      // focus trap
      document.removeEventListener("focus", this.focustrap);

      // esc
      this.$events.$off("keydown.esc", this.close, false);
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
