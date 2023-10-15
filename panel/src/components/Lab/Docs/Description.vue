<template>
	<section
		v-if="description?.length || since?.length"
		class="k-lab-docs-section"
	>
		<header class="k-lab-docs-desc-header">
			<k-headline class="h3">Description</k-headline>
			<k-text v-if="since?.length">
				Since <code>{{ since }}</code>
			</k-text>
		</header>

		<k-box theme="text">
			<k-text :html="description" />
		</k-box>
	</section>
</template>

<script>
export const props = {
	props: {
		description: String,
		since: String
	}
};

export default {
	mixins: [props]
};
</script>

<style>
.k-lab-docs-desc-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
}
</style>
