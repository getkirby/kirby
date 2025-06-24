<template>
	<k-lab-examples>
		<k-lab-example label="Landscape">
			<k-image-file-preview
				:details="details"
				:image="image"
				url="https://getkirby.com"
				class="k-file-preview"
			/>
		</k-lab-example>
		<k-lab-example label="Portrait">
			<k-image-file-preview
				:details="details"
				:image="{
					...image,
					src: 'https://picsum.photos/800/1200'
				}"
				url="https://getkirby.com"
				class="k-file-preview"
			/>
		</k-lab-example>
		<k-lab-example label="Square">
			<k-image-file-preview
				:details="details"
				:image="{
					...image,
					src: 'https://picsum.photos/800/800'
				}"
				url="https://getkirby.com"
				class="k-file-preview"
			/>
		</k-lab-example>
		<k-lab-example label="Focusable">
			<k-image-file-preview
				:focusable="true"
				:details="details"
				:image="{
					...image,
					src: 'https://picsum.photos/800/800'
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
				{ title: "URL", text: "some-image.jpg", link: "https://getkirby.com" },
				{ title: "Size", text: "1.2 MB" },
				{ title: "Dimensions", text: "1220 × 800" },
				{ title: "Orientation", text: "square" }
			];
		},
		image() {
			return {
				src: "https://picsum.photos/1200/800"
			};
		}
	}
};
</script>
