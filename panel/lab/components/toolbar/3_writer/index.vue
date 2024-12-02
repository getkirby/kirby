<template>
	<k-lab-examples>
		<k-lab-example label="inline">
			<k-writer-input />
		</k-lab-example>
		<k-lab-example label="not inline">
			<k-writer-input :toolbar="{ inline: false }" />
		</k-lab-example>
		<k-lab-example label="a few nodes and marks">
			<k-writer-input
				:toolbar="{
					inline: false,
					nodes: ['bulletList', 'orderedList'],
					marks: ['bold', 'italic', '|', 'link']
				}"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {}
};
</script>

<style>
.k-writer-input {
	background-color: var(--input-color-back);
	border-radius: var(--rounded);
}
</style>
