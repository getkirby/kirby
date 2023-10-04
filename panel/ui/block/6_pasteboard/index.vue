<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-button icon="open" variant="filled" @click="openPasteboard">
				Open
			</k-button>
		</k-ui-example>
	</k-ui-examples>
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
