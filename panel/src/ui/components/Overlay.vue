<template>
  <portal v-if="isOpen">
    <slot :close="close" :isOpen="isOpen" />
  </portal>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    visible: Boolean
  },
  data() {
    return {
      isOpen: this.visible,
      scrollTop: 0
    };
  },
  created() {
    this.$events.$on("keydown.esc", this.close, false);
  },
  destroyed() {
    this.$events.$off("keydown.esc", this.close, false);
  },
  mounted() {
    if (this.visible === true) {
      this.$emit("open");
    }
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
      if (this.loading) {
        return false;
      }

      this.isOpen = false;
      this.$emit("close");
      this.$events.$off("keydown.esc", this.close);
      this.restoreScrollPosition();
    }
  }
};
</script>

