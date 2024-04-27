<template>
	<k-file-preview :details="details" :options="options">
		<template #thumb>
			<audio controls preload="metadata" :src="url" />
		</template>
	</k-file-preview>
</template>

<script>
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
