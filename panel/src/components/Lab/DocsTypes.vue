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
	color: light-dark(var(--color-gray-800), var(--color-gray-900));
	outline-color: light-dark(var(--color-gray-400), var(--color-gray-900));
	background: var(--color-gray-300);
}
.k-lab-docs-types code:is([data-type="boolean"], [data-type="Boolean"]) {
	color: light-dark(var(--color-purple-800), var(--color-purple-900));
	outline-color: light-dark(var(--color-purple-400), var(--color-purple-900));
	background: var(--color-purple-300);
}
.k-lab-docs-types code:is([data-type="string"], [data-type="String"]) {
	color: light-dark(var(--color-green-800), var(--color-green-900));
	outline-color: light-dark(var(--color-green-500), var(--color-green-900));
	background: var(--color-green-300);
}
.k-lab-docs-types code:is([data-type="number"], [data-type="Number"]) {
	color: light-dark(var(--color-orange-800), var(--color-orange-900));
	outline-color: light-dark(var(--color-orange-500), var(--color-orange-900));
	background: var(--color-orange-300);
}
.k-lab-docs-types code:is([data-type="array"], [data-type="Array"]) {
	color: light-dark(var(--color-aqua-800), var(--color-aqua-900));
	outline-color: light-dark(var(--color-aqua-500), var(--color-aqua-900));
	background: var(--color-aqua-300);
}
.k-lab-docs-types code:is([data-type="object"], [data-type="Object"]) {
	color: light-dark(var(--color-yellow-800), var(--color-yellow-900));
	outline-color: light-dark(var(--color-yellow-500), var(--color-yellow-900));;
	background: var(--color-yellow-300);
}
.k-lab-docs-types code[data-type="func"] {
	color: light-dark(var(--color-pink-800), var(--color-pink-900));
	outline-color: var(--color-pink-400);
	background: var(--color-pink-300);
}
</style>
