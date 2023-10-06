<template>
	<k-lab-examples>
		<k-lab-example label="empty">
			<k-collection :empty="empty" />
		</k-lab-example>
		<k-lab-example label="cards">
			<k-collection :items="items" :empty="empty" layout="cards" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		empty: Object,
		items: Array,
	},
};
</script>
