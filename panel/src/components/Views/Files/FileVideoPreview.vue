<template>
	<k-file-preview
		:details="details"
		:options="options"
		class="k-file-video-preview"
	>
		<video controls preload="metadata" :src="url" />
	</k-file-preview>
</template>

<script>
/**
 * @since 4.3.0
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

@container (min-width: 65rem) {
	.k-file-video-preview {
		grid-template-columns: 50% auto;
	}
	.k-file-video-preview .k-file-preview-thumb-column {
		aspect-ratio: 16/9;
	}
}
</style>
