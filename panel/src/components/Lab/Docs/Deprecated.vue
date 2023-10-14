<template>
	<section
		v-if="deprecated.length"
		class="k-lab-docs-section k-lab-docs-deprecated"
	>
		<k-box icon="protected" theme="warning">
			<k-text :html="'<strong>Deprecated:</strong>' + deprecated" />
		</k-box>
	</section>
</template>

<script>
export const props = {
	props: {
		deprecated: String
	}
};

export default {
	mixins: [props]
};
</script>

<style>
.k-lab-docs-deprecated .k-box {
	box-shadow: var(--shadow);
}
</style>
