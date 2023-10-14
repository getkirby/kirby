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
 *
 * @example <k-dropzone @drop="onDrop">
 *  <div />
 * </k-dropzone>
 */
export default {
	props: {
		/**
		 * Enable/disable the dropzone
		 */
		disabled: {
			type: Boolean
		}
	},
	emits: ["drop"],
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

			this.files = $event.dataTransfer.files;
			/**
			 * Files have been dropped into the dropzone
			 * @property {array} files the files list (can be used e.g. to start an upload)
			 */
			this.$emit("drop", this.files);
			this.$events.emit("dropzone.drop");
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
	inset: 0;
	display: none;
	pointer-events: none;
	z-index: 1;
	border-radius: var(--rounded);
}
.k-dropzone[data-over="true"]::after {
	display: block;
	background: hsla(var(--color-blue-hs), var(--color-blue-l-300), 0.6);
	outline: var(--outline);
}
</style>
