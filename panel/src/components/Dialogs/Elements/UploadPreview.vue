<template>
	<a :href="url" target="_blank">
		<k-image v-if="isPreviewable" :cover="true" :src="url" back="pattern" />
		<k-icon-frame
			v-else
			:color="color"
			:icon="icon ?? fallbackIcon"
			back="black"
			ratio="1/1"
		/>
	</a>
</template>

<script>
/**
 * @since 4.3.0
 * @internal
 */
export default {
	props: {
		color: String,
		icon: String,
		mime: String,
		url: String
	},
	computed: {
		fallbackIcon() {
			if (this.mime.startsWith("image/")) {
				return "file-image";
			}

			if (this.mime.startsWith("audio/")) {
				return "file-audio";
			}

			if (this.mime.startsWith("video/")) {
				return "file-video";
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
			].includes(this.mime);
		}
	}
};
</script>
