<template>
  <div :data-dragging="dragging" :data-over="over" class="kirby-dropzone">
    <div v-show="dragging" class="kirby-dropzone-overlay">
      <p><kirby-icon type="download" /> {{ label }}</p>
    </div>
    <div class="kirby-dropzone-content">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      default: "Drop to upload"
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      files: [],
      dragging: false,
      over: false
    };
  },
  mounted() {
    this.$events.$on("dragenter", this.start);
    this.$events.$on("dragleave", this.stop);
    this.$events.$on("drop", this.cancel);

    this.$el.addEventListener("dragover", this.enter, false);
    this.$el.addEventListener("dragleave", this.leave, false);
    this.$el.addEventListener("drop", this.drop, false);

    this.$events.$on("dropzone.drop", this.reset, false);
  },
  destroyed() {
    this.$events.$off("dragenter", this.start);
    this.$events.$off("dragleave", this.stop);
    this.$events.$off("drop", this.cancel);

    this.$el.removeEventListener("dragover", this.enter, false);
    this.$el.removeEventListener("dragleave", this.leave, false);
    this.$el.removeEventListener("drop", this.drop, false);

    this.$events.$off("dropzone.drop", this.reset, false);
  },
  methods: {
    cancel() {
      this.reset();
    },
    reset() {
      this.dragging = false;
      this.over = false;
    },
    start() {
      if (this.disabled === false) {
        this.dragging = true;
      }
    },
    stop() {
      this.reset();
    },
    enter(e) {
      e.dataTransfer.dropEffect = "copy";
      this.over = true;
    },
    leave() {
      this.over = false;
    },
    drop(e) {
      if (this.disabled) {
        return;
      }

      this.$events.$emit("dropzone.drop");

      let files = e.target.files || e.dataTransfer.files;
      this.files = files;
      this.$emit("drop", files);
    }
  }
};
</script>

<style lang="scss">
.kirby-dropzone {
  position: relative;
}
.kirby-dropzone[data-dragging] .kirby-dropzone-content {
  opacity: 0.5;
  min-height: 5rem;
}
.kirby-dropzone-overlay {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  background: rgba($color-dark, 0.7);
  box-shadow: $color-focus-on-dark 0 0 0 3px;
  z-index: z-index("dropzone");
  border-radius: $border-radius;
}
.kirby-dropzone-overlay p {
  background: $color-dark;
  font-size: $font-size-small;
  color: $color-white;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  border-radius: 2rem;
}
.kirby-dropzone[data-over] .kirby-dropzone-overlay {
  box-shadow: $color-positive-on-dark 0 0 0 3px;
}
.kirby-dropzone[data-over] .kirby-dropzone-overlay p {
  background: $color-positive-on-dark;
  color: $color-dark;
}

.kirby-dropzone-overlay .kirby-icon {
  margin-right: 0.75rem;
}
</style>
