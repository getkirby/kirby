<template>
	<k-lab-examples>
		<k-lab-example label="Video">
			<k-video-file-preview
				:details="details"
				url="https://getkirby.com/media/pages/releases/4-0/design/2220cded71-1704303353/panel.mp4"
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
				{ title: "Template", text: "custom-video" },
				{ title: "Mime", text: "video/mp4" },
				{ title: "URL", text: "some-video.mp4", link: "https://getkirby.com" },
				{ title: "Size", text: "1.2 MB" },
				{ title: "Dimensions", text: "1220 × 800" },
				{ title: "Length", text: "12 minutes" }
			];
		}
	}
};
</script>
