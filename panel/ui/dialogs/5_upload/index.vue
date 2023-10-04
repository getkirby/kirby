<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="Empty">
			<k-button icon="open" variant="filled" @click="openEmpty">
				Open
			</k-button>
		</k-ui-example>
		<k-ui-example :flex="true" label="Upload error">
			<k-button icon="open" variant="filled" @click="openUploadError">
				Open
			</k-button>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	methods: {
		openEmpty() {
			this.$panel.upload.reset();
			this.$panel.upload.open();
		},
		openUploadError() {
			this.$panel.upload.open();
			this.$panel.upload.files = [
				{
					name: "test-0",
					extension: "jpg",
					url: "https://picsum.photos/600/850",
					type: "image/jpeg",
					niceSize: "80kb",
				},
				{
					name: "test-1",
					extension: "jpg",
					niceSize: "80kb",
					url: "https://picsum.photos/600/850",
					type: "image/jpeg",
					error:
						"Thisisaverylongerrormessagethatcouldbreakthelayoutoftheuploaderandmaybeevenshouldbecauseotherwisewedon'tseeit.",
				},
				{
					name: "test-2",
					extension: "jpg",
					niceSize: "80kb",
					url: "https://picsum.photos/600/850",
					type: "image/jpeg",
					error:
						"This one should wrap proplery because it has some spaces and isn't completely bonkers",
				},
			];
		},
	},
};
</script>
