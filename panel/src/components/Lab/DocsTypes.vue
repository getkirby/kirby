<template>
	<k-text class="k-lab-docs-types">
		<code v-for="type in types" :key="type" :data-type="type">
			{{ type }}
		</code>
	</k-text>
</template>

<script>
export default {
	props: {
		types: {
			type: Array,
			default: () => []
		}
	}
};
</script>

<style>
.k-lab-docs-types {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}

.k-lab-docs-types.k-text code {
	color: var(--color-gray-800);
	outline-color: var(--color-gray-400);
	background: var(--color-gray-300);
}
.k-lab-docs-types code:is([data-type="boolean"], [data-type="Boolean"]) {
	color: var(--color-purple-800);
	outline-color: var(--color-purple-400);
	background: var(--color-purple-300);
}
.k-lab-docs-types code:is([data-type="string"], [data-type="String"]) {
	color: var(--color-green-800);
	outline-color: var(--color-green-500);
	background: var(--color-green-300);
}
.k-lab-docs-types code:is([data-type="number"], [data-type="Number"]) {
	color: var(--color-orange-800);
	outline-color: var(--color-orange-500);
	background: var(--color-orange-300);
}
.k-lab-docs-types code:is([data-type="array"], [data-type="Array"]) {
	color: var(--color-aqua-800);
	outline-color: var(--color-aqua-500);
	background: var(--color-aqua-300);
}
.k-lab-docs-types code:is([data-type="object"], [data-type="Object"]) {
	color: var(--color-yellow-800);
	outline-color: var(--color-yellow-500);
	background: var(--color-yellow-300);
}
.k-lab-docs-types code[data-type="func"] {
	color: var(--color-pink-800);
	outline-color: var(--color-pink-400);
	background: var(--color-pink-300);
}
</style>
