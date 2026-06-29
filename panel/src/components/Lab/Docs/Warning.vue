<template>
	<section v-if="text.length" class="k-lab-docs-section k-lab-docs-warning">
		<k-lab-docs-warning v-bind="$props" />
	</section>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	props: {
		icon: {
			type: String,
			default: "protected"
		},
		title: String,
		text: String
	}
};
</script>

<style>
.k-lab-docs-warning .k-box {
	box-shadow: var(--shadow);
}
</style>
