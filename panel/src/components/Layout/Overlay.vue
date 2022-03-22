<template>
  <portal v-if="isOpen">
    <div
      ref="overlay"
      :data-centered="loading || centered"
      :data-dimmed="dimmed"
      :data-loading="loading"
      :dir="$translation.direction"
      :class="$vnode.data.staticClass"
      class="k-overlay"
      v-on="$listeners"
      @mousedown="close"
    >
      <k-loader v-if="loading" class="k-overlay-loader" />
      <slot v-else :close="close" :isOpen="isOpen" />
    </div>
  </portal>
</template>

<script>
export default {
  inheritAttrs: true,
  props: {
    autofocus: {
      type: Boolean,
      default: true
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
    close() {
      // it makes it run once
      if (this.isOpen === false) {
        return;
      }

      this.isOpen = false;
      this.$emit("close");
      this.restoreScrollPosition();

      // unbind events
      this.$events.$off("keydown.esc", this.close);
    },
    focus() {
      let target = this.$refs.overlay.querySelector(`
        [autofocus],
        [data-autofocus]
      `);

      if (target === null) {
        target = this.$refs.overlay.querySelector(`
          input,
          textarea,
          select,
          button
        `);
      }

      if (typeof target?.focus === "function") {
        return target.focus();
      }

      if (typeof this.$slots.default[0]?.context?.focus === "function") {
        return this.$slots.default[0].context.focus();
      }
    },
    open() {
      // it makes it run once
      if (this.isOpen === true) {
        return;
      }

      this.storeScrollPosition();
      this.isOpen = true;
      this.$emit("open");

      // bind events
      this.$events.$on("keydown.esc", this.close);

      setTimeout(() => {
        // autofocus
        if (this.autofocus === true) {
          this.focus();
        }

        // prevent that clicks on the overlay slot trigger close
        document
          .querySelector(".k-overlay > *")
          .addEventListener("mousedown", (e) => e.stopPropagation());

        this.$emit("ready");
      }, 1);
    },
    restoreScrollPosition() {
      const view = document.querySelector(".k-panel-view");

      if (view?.scrollTop) {
        view.scrollTop = this.scrollTop;
      }
    },
    storeScrollPosition() {
      const view = document.querySelector(".k-panel-view");

      if (view?.scrollTop) {
        this.scrollTop = view.scrollTop;
      } else {
        this.scrollTop = 0;
      }
    }
  }
};
</script>

<style>
.k-overlay {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: var(--z-dialog);
  transform: translate3d(0, 0, 0);
}
.k-overlay[data-centered="true"] {
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-overlay[data-dimmed="true"] {
  background: var(--color-backdrop);
}
.k-overlay-loader {
  color: var(--color-white);
}
</style>
