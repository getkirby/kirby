<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-button icon="open" variant="filled" @click="openSelector">
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example label="Headline">
			<k-button
				icon="open"
				variant="filled"
				@click="openSelector({ headline: 'Convert to …' })"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example label="Groups">
			<k-button icon="open" variant="filled" @click="openSelectorWithGroups">
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		openSelector(props = {}) {
			this.$panel.dialog.open({
				component: "k-block-selector",
				props: {
					fieldsets: {
						code: {
							icon: "code",
							name: "Code",
						},
						heading: {
							icon: "title",
							name: "Heading",
						},
						text: {
							icon: "text",
							name: "Text",
						},
					},
					...props,
				},
			});
		},
		openSelectorWithGroups() {
			this.$panel.dialog.open({
				component: "k-block-selector",
				props: {
					fieldsets: {
						code: {
							icon: "code",
							name: "Code",
						},
						heading: {
							icon: "title",
							name: "Heading",
						},
						image: {
							icon: "image",
							name: "Image",
						},
						text: {
							icon: "text",
							name: "Text",
						},
						video: {
							icon: "video",
							name: "Video",
						},
					},
					fieldsetGroups: {
						media: {
							label: "Media",
							sets: ["image", "video"],
						},
						text: {
							label: "Text",
							sets: ["code", "heading", "text"],
						},
					},
				},
			});
		},
	},
};
</script>
