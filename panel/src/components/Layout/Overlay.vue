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
  inheritAttrs: true,
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
  destroyed() {
    // make sure that the close events are properly triggered
    // if a dialog has not been closed by the close method
    this.close(true);
  },
  methods: {
    close(force = false) {
      // it makes it run once
      if (this.isOpen === false) {
        return;
      }

      this.isOpen = false;

      // restore scrolling
      if (force === true || document.querySelectorAll('.k-overlay').length === 1) {
        this.restoreOverflow();
        this.restoreScrollPosition();
      }

      // unbind events
      this.$events.$off("keydown.esc", this.close);
      this.$emit("close");
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

      if (target && typeof target.focus === "function") {
        target.focus();
        return;
      }

      if (
        this.$slots.default[0] &&
        this.$slots.default[0].context &&
        typeof this.$slots.default[0].context.focus === "function") {
        this.$slots.default[0].context.focus();
        return;
      }
    },
    open() {
      // it makes it run once
      if (this.isOpen === true) {
        return;
      }

      this.isOpen = true;
      this.$emit("open");

      // bind events
      this.$events.$on("keydown.esc", this.close);

      // prevent scrolling for the body element
      if (document.body.style.position !== "fixed") {
        this.storeScrollPosition();
        document.body.style.position = "fixed";
        document.body.style.width = "100%";
        document.body.style.top = -(this.scrollTop) + "px";
      }

      setTimeout(() => {
        // autofocus
        if (this.autofocus === true) {
          this.focus();
        }

        // prevent that clicks on the overlay slot trigger close
        document.querySelector(".k-overlay > *").addEventListener("mousedown", e => e.stopPropagation());

        this.$emit("ready");
      }, 1)
    },
    restoreOverflow() {
      document.body.style.position = "static";
      document.body.style.top = 0;
    },
    restoreScrollPosition() {
      document.documentElement.scrollTop = this.scrollTop;
    },
    storeScrollPosition() {
      this.scrollTop = document.documentElement.scrollTop;
    },
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
.k-overlay[data-centered] {
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-overlay[data-dimmed] {
  background: var(--color-backdrop);
}
.k-overlay-loader {
  color: var(--color-white);
}
</style>
