<template>
	<k-lab-examples>
		<k-lab-example label="Unstyled">
			<k-box
				:button="true"
				icon="add"
				theme="empty"
				text="Click to add something …"
				@click.native="$panel.notification.success('Yay, you clicked the box')"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		themes: Array
	}
};
</script>
