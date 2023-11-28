<template>
	<k-lab-examples>
		<k-lab-example label="List">
			<k-items :items="items" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		items: Array
	}
};
</script>
