<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-button icon="open" variant="filled" @click="openPasteboard">
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		openPasteboard(props = {}) {
			this.$panel.dialog.open({
				component: "k-block-pasteboard",
				props: {},
			});
		},
	},
};
</script>
