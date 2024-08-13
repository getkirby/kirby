<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Empty">
			<k-button icon="open" variant="filled" @click="openEmpty">
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Custom Preview">
			<k-button icon="open" variant="filled" @click="openCustomPreview">
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Upload error">
			<k-button icon="open" variant="filled" @click="openUploadError">
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		openCustomPreview() {
			this.$panel.upload.open();
			this.$panel.upload.files = [
				{
					back: "red",
					cover: false,
					name: "test-0",
					extension: "jpg",
					url: "https://picsum.photos/600/850",
					type: "image/jpeg",
					niceSize: "80kb"
				},
				{
					back: "orange",
					cover: false,
					name: "test-1",
					extension: "jpg",
					niceSize: "80kb",
					url: "https://picsum.photos/850/600",
					type: "image/jpeg"
				},
				{
					back: "green",
					cover: false,
					name: "test-2",
					extension: "jpg",
					niceSize: "80kb",
					url: "https://picsum.photos/400/850",
					type: "image/jpeg"
				}
			];
		},
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
					niceSize: "80kb"
				},
				{
					name: "test-1",
					extension: "jpg",
					niceSize: "80kb",
					url: "https://picsum.photos/600/850",
					type: "image/jpeg",
					error:
						"Thisisaverylongerrormessagethatcouldbreakthelayoutoftheuploaderandmaybeevenshouldbecauseotherwisewedon'tseeit."
				},
				{
					name: "test-2",
					extension: "jpg",
					niceSize: "80kb",
					url: "https://picsum.photos/600/850",
					type: "image/jpeg",
					error:
						"This one should wrap proplery because it has some spaces and isn't completely bonkers"
				}
			];
		}
	}
};
</script>
