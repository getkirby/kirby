<template>
	<k-ui-examples>
		<k-ui-example label="page tree">
			<k-page-tree :current="selected?.value" @select="selected = $event" />
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			selected: null,
		};
	},
};
</script>
