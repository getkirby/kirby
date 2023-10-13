<template>
	<section v-if="deprecated.length" class="k-lab-docs-section">
		<k-box icon="alert" theme="warning">
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
