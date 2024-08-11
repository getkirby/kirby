<template>
	<a :href="url" class="k-upload-item-preview" target="_blank">
		<k-image
			v-if="isPreviewable"
			:cover="cover"
			:src="url"
			:back="back ?? 'pattern'"
		/>
		<k-icon-frame
			v-else
			:color="color ?? fallbackColor"
			:icon="icon ?? fallbackIcon"
			:back="back ?? 'black'"
			ratio="1/1"
		/>
	</a>
</template>

<script>
export const props = {
	props: {
		/**
		 * Preview back color
		 */
		back: String,
		/**
		 * Preview icon color
		 */
		color: String,
		cover: {
			type: Boolean,
			default: true
		},
		/**
		 * Preview icon type
		 */
		icon: String,
		/**
		 * MIME type
		 */
		type: String,
		/**
		 * Upload URL
		 */
		url: String
	}
};

/**
 * Preview an upload with its image or
 * a representative icon
 * @since 4.3.0
 */
export default {
	mixins: [props],
	computed: {
		fallbackColor() {
			if (this.type?.startsWith("image/")) {
				return "orange-500";
			}

			if (this.type?.startsWith("audio/")) {
				return "aqua-500";
			}

			if (this.type?.startsWith("video/")) {
				return "yellow-500";
			}

			return "white";
		},
		fallbackIcon() {
			if (this.type?.startsWith("image/")) {
				return "image";
			}

			if (this.type?.startsWith("audio/")) {
				return "audio";
			}

			if (this.type?.startsWith("video/")) {
				return "video";
			}

			return "file";
		},
		isPreviewable() {
			return [
				"image/jpeg",
				"image/jpg",
				"image/gif",
				"image/png",
				"image/webp",
				"image/avif",
				"image/svg+xml"
			].includes(this.type);
		}
	}
};
</script>

<style>
.k-upload-item-preview {
	--icon-size: 24px;
	grid-area: preview;
	display: flex;
	aspect-ratio: 1/1;
	width: 100%;
	height: 100%;
	overflow: hidden;
	border-start-start-radius: var(--rounded);
	border-end-start-radius: var(--rounded);
}
.k-upload-item-preview:focus {
	border-radius: var(--rounded);
	outline: 2px solid var(--color-focus);
	z-index: 1;
}
</style>
