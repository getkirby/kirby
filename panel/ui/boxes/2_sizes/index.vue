<template>
	<k-ui-examples>
		<k-ui-example label="Cardlet Height">
			<k-box
				icon="box"
				align="center"
				text="This is a box"
				theme="empty"
				height="var(--item-height-cardlet)"
			/>
		</k-ui-example>
		<k-ui-example label="Custom Height">
			<k-box
				icon="box"
				align="center"
				text="This is a box"
				theme="empty"
				height="10rem"
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
