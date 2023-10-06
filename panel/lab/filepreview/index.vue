<template>
	<k-lab-examples>
		<k-lab-example label="Lanscape">
			<k-file-preview
				:focus="focus"
				:focusable="true"
				:details="details"
				:image="image"
				url="https://getkirby.com"
				@focus="focus = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Portrait">
			<k-file-preview
				:focus="focus"
				:focusable="true"
				:details="details"
				:image="{
					...image,
					src: 'https://picsum.photos/800/1200',
				}"
				url="https://getkirby.com"
				@focus="focus = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Square">
			<k-file-preview
				:focus="focus"
				:focusable="true"
				:details="details"
				:image="{
					...image,
					src: 'https://picsum.photos/800/800',
				}"
				url="https://getkirby.com"
				@focus="focus = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Icon">
			<k-file-preview
				:details="details"
				:image="{
					icon: 'markdown',
					color: 'orange-600',
				}"
				url="https://getkirby.com"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			focus: null,
		};
	},
	computed: {
		details() {
			return [
				{ title: "Template", text: "custom-image" },
				{ title: "Mime", text: "image/jpeg" },
				{ title: "URL", text: "some-image.jpg", link: "https://getkirby.com" },
				{ title: "Size", text: "1.2 MB" },
				{ title: "Dimensions", text: "1220 × 800" },
				{ title: "Orientation", text: "square" },
			];
		},
		image() {
			return {
				src: "https://picsum.photos/1200/800",
			};
		},
	},
};
</script>
