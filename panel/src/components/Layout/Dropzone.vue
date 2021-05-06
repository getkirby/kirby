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
    <!-- @slot Everything that should be covered by the dropzone -->
    <slot />
  </div>
</template>

<script>
/**
 * The dropzone component helps to simplify creating areas, where files can be dropped and uploaded or displayed. You simply wrap it around any other element to create the zone. The dropzone will also create a focus ring around the area when the user drags files over it.

 */
export default {
  props: {
    /**
     * You can deactivate the dropzone with this property
     */
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
      /**
       * The drop event is triggered when files are being dropped into the dropzone. 
       * @event drop
       * @property {array} files The event receives the files list as argument, which can then be used to start an upload for example.
       */
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

<style>
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
  outline: 1px solid var(--color-focus);
  box-shadow: var(--color-focus-outline) 0 0 0 3px;
}
</style>
