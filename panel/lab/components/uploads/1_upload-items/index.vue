<template>
	<k-lab-examples>
		<k-lab-example label="Upload Items">
			<k-upload-items :items="items" @remove="remove" @rename="rename" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		items() {
			return [
				{
					extension: "jpeg",
					type: "image/jpeg",
					name: "test",
					niceSize: "128 KB",
					url: "https://picsum.photos/200/200"
				},
				{
					extension: "pdf",
					type: "application/pdf",
					name: "document",
					niceSize: "1.5 MB",
					url: "/some.pdf"
				},
				{
					extension: "mp4",
					type: "video/mp4",
					name: "screencast",
					niceSize: "3 GB",
					url: "/some.mp4"
				},
				{
					extension: "mp3",
					type: "audio/mp3",
					name: "song",
					niceSize: "2.5 MB",
					url: "/some.mp3"
				}
			];
		}
	},
	methods: {
		remove(file) {
			console.log("Remove", { file });
		},
		rename(file, name) {
			console.log("Rename", { file, name });
		}
	}
};
</script>
