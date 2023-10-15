<template>
	<section v-if="docBlock?.length" class="k-lab-docs-section">
		<header>
			<k-headline class="h3">Further information</k-headline>
		</header>

		<k-box theme="text">
			<k-text :html="docBlock" />
		</k-box>
	</section>
</template>

<script>
export const props = {
	props: {
		docBlock: String
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
