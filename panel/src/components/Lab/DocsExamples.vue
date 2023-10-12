<template>
	<section v-if="examples.length" class="k-lab-docs-section">
		<k-headline class="h3">Examples</k-headline>
		<k-code v-for="(example, index) in examples" :key="index" language="html">{{
			example.content
		}}</k-code>
	</section>
</template>

<script>
export const props = {
	props: {
		examples: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props]
};
</script>
