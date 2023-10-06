<template>
	<k-lab-examples>
		<k-lab-example label="page tree">
			<k-page-tree :current="selected?.value" @select="selected = $event" />
		</k-lab-example>
	</k-lab-examples>
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
