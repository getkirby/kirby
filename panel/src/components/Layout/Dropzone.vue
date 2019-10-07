<template>
  <div
    :data-dragging="dragging"
    :data-over="over"
    class="k-dropzone"
    @dragenter="onEnter"
    @dragleave="onLeave"
    @dragover="onOver"
    @drop="onDrop"
  >
    <slot />
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
  methods: {
    cancel() {
      this.reset();
    },
    reset() {
      this.dragging = false;
      this.over = false;
    },
    onDrop($event) {
      if (this.disabled === true) {
        return this.reset();
      }

      if (this.$helper.isUploadEvent($event) === false) {
        return this.reset();
      }

      this.$events.$emit("dropzone.drop");

      this.files = $event.dataTransfer.files;
      this.$emit("drop", this.files);
      this.reset();
    },
    onEnter($event) {
      if (this.disabled === false && this.$helper.isUploadEvent($event)) {
        this.dragging = true;
      }
    },
    onLeave() {
      this.reset();
    },
    onOver($event) {
      if (this.disabled === false && this.$helper.isUploadEvent($event)) {
        $event.dataTransfer.dropEffect = "copy";
        this.over = true;
      }
    }
  }
};
</script>

<style lang="scss">
.k-dropzone {
  position: relative;
}
.k-dropzone::after {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: none;
  pointer-events: none;
  z-index: 1;
}
.k-dropzone[data-over]::after {
  display: block;
  outline: 1px solid $color-focus;
  box-shadow: $color-focus-outline 0 0 0 3px;
}

</style>
