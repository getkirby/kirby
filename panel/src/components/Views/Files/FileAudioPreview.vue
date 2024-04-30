<template>
	<k-file-preview
		:details="details"
		:options="options"
		class="k-file-audio-preview"
	>
		<audio controls preload="metadata" :src="url" />
	</k-file-preview>
</template>

<script>
/**
 * File view preview for audio files
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
