<template>
	<div class="k-video-file-preview">
		<k-file-preview-frame :options="options">
			<video controls preload="metadata" :src="url" />
		</k-file-preview-frame>

		<k-file-preview-details :details="details" />
	</div>
</template>

<script>
/**
 * File view preview for video files
 * @since 5.0.0
 */
export default {
	props: {
		details: Array,
		url: String
	},
	computed: {
		options() {
			return [
				{
					icon: "download",
					text: this.$t("download"),
					link: this.url,
					download: true
				}
			];
		}
	}
};
</script>

<style>
.k-video-file-preview .k-file-preview-frame-column {
	aspect-ratio: 16/9;
}

@container (min-width: 60rem) {
	.k-video-file-preview {
		grid-template-columns: 50% auto;
	}
}
</style>
