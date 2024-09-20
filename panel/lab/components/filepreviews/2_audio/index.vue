<template>
	<k-lab-examples>
		<k-lab-example label="Audio">
			<k-audio-file-preview
				:details="details"
				url="https://media.rss.com/devslove-it-der-podcast/2024_08_17_06_31_26_02e80083-a84e-4e0c-bc15-9973bae93244.mp3"
				class="k-file-preview"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		details() {
			return [
				{ title: "Template", text: "custom-audio" },
				{ title: "Mime", text: "audio/mp3" },
				{ title: "URL", text: "some-audio.mp3", link: "https://getkirby.com" },
				{ title: "Size", text: "1.2 MB" },
				{ title: "Length", text: "12 minutes" }
			];
		}
	}
};
</script>
