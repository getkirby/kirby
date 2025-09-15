<template>
	<section v-if="description?.length" class="k-lab-docs-section">
		<header>
			<k-headline class="h3">Description</k-headline>
		</header>

		<k-box theme="text">
			<k-text :html="description" />
		</k-box>
	</section>
</template>

<script>
export const props = {
	props: {
		description: String
	}
};

export default {
	mixins: [props]
};
</script>
