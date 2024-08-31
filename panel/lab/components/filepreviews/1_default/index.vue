<template>
	<k-lab-examples>
		<k-lab-example>
			<k-default-file-preview
				:details="details"
				:image="{
					icon: 'markdown',
					color: 'orange-600'
				}"
				url="https://getkirby.com"
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
				{ title: "Template", text: "custom-image" },
				{ title: "Mime", text: "image/jpeg" },
				{ title: "URL", text: "some-file.pdf", link: "https://getkirby.com" },
				{ title: "Size", text: "1.2 MB" }
			];
		}
	}
};
</script>
