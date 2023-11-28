<template>
	<k-lab-examples>
		<k-lab-example label="cardlets">
			<k-items :items="items" layout="cardlets" />
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
