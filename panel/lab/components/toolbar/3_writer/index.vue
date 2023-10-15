<template>
	<k-lab-examples>
		<k-lab-example label="inline">
			<div class="k-writer-input"><k-writer /></div>
		</k-lab-example>
		<k-lab-example label="not inline">
			<div class="k-writer-input">
				<k-writer :toolbar="{ inline: false }" />
			</div>
		</k-lab-example>
		<k-lab-example label="a few nodes and marks">
			<div class="k-writer-input">
				<k-writer
					:toolbar="{
						inline: false,
						nodes: ['bulletList', 'orderedList'],
						marks: ['bold', 'italic', '|', 'link']
					}"
				/>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {}
};
</script>

<style>
.k-writer {
	background-color: white;
	border-radius: var(--rounded);
}
</style>
