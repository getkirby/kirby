<template>
	<div class="k-file-video-preview">
		<k-file-preview-thumb :options="options">
			<video controls preload="metadata" :src="url" />
		</k-file-preview-thumb>
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
		details: {
			default: () => [],
			type: Array
		},
		image: {
			default: () => ({}),
			type: Object
		},
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
.k-file-video-preview .k-file-preview-thumb-column {
	aspect-ratio: 16/9;
}
</style>
