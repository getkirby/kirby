<template>
	<k-ui-examples>
		<k-ui-example label="Unstyled">
			<k-box
				:button="true"
				icon="add"
				theme="empty"
				text="Click to add something …"
				@click.native="$panel.notification.success('Yay, you clicked the box')"
			/>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		themes: Array,
	},
};
</script>
