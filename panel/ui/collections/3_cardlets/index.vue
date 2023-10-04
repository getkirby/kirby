<template>
	<k-ui-examples>
		<k-ui-example label="empty">
			<k-collection :empty="empty" />
		</k-ui-example>
		<k-ui-example label="cards">
			<k-collection :items="items" :empty="empty" layout="cardlets" />
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		empty: Object,
		items: Array,
	},
};
</script>
