<template>
	<k-ui-examples>
		<k-ui-example label="custom">
			<k-grid style="--columns: 2; gap: 3rem">
				<div>
					<k-headline>Browse …</k-headline>
					<k-browser :items="items" :selected="selected?.value" @select="selected = $event" />
				</div>
				<div v-if="selected">
					<k-headline>Selected</k-headline>
					<k-code>{{ selected }}</k-code>
				</div>
			</k-grid>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		items: Array,
	},
	data() {
		return {
			selected: null,
		};
	},
};
</script>

<style>
.k-ui-example .k-headline {
	margin-bottom: 0.5rem;
}
.k-ui-example .k-browser {
	border: 1px solid var(--color-border);
	background: var(--color-white);
	border-radius: var(--rounded);
	padding: var(--spacing-2);
}
</style>
