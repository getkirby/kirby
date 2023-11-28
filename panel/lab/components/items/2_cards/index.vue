<template>
	<k-lab-examples>
		<k-lab-example label="cards">
			<k-items :items="items" layout="cards" />
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
