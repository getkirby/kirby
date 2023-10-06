<template>
	<k-lab-examples>
		<k-lab-example label="Cardlet Height">
			<k-box
				icon="box"
				align="center"
				text="This is a box"
				theme="empty"
				height="var(--item-height-cardlet)"
			/>
		</k-lab-example>
		<k-lab-example label="Custom Height">
			<k-box
				icon="box"
				align="center"
				text="This is a box"
				theme="empty"
				height="10rem"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		themes: Array,
	},
};
</script>
