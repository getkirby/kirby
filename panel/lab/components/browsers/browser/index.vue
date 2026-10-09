<template>
	<k-lab-examples>
		<k-lab-example label="custom">
			<k-grid style="--columns: 2; gap: 3rem">
				<div>
					<k-headline>Browse …</k-headline>
					<k-browser
						:items="items"
						:selected="selected?.value"
						@select="selected = $event"
					/>
				</div>
				<div v-if="selected">
					<k-headline>Selected</k-headline>
					<k-code>{{ selected }}</k-code>
				</div>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		items: Array
	},
	data() {
		return {
			selected: null
		};
	}
};
</script>

<style>
.k-lab-example .k-headline {
	margin-bottom: 0.5rem;
}
</style>
