<template>
	<k-lab-examples>
		<k-lab-example>
			<k-file-preview-details :details="details" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		details() {
			return [
				{ title: "Template", text: "custom-image" },
				{ title: "Mime", text: "image/jpeg" },
				{ title: "URL", text: "some-image.jpg", link: "https://getkirby.com" },
				{ title: "Size", text: "1.2 MB" },
				{ title: "Dimensions", text: "1220 × 800" },
				{ title: "Orientation", text: "square" }
			];
		}
	}
};
</script>

<style>
.k-lab-example-canvas .k-file-preview-details {
	background-color: var(--color-gray-900);
}
</style>
