<template>
	<ul v-if="params.length" class="k-labs-docs-params">
		<li v-for="param in params" :key="param.name">
			<k-text>
				<code>{{ param.name }}</code>
				<k-lab-docs-types :types="[param.type]" />
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-if="param.description.length" v-html="param.description" />
			</k-text>
		</li>
	</ul>
</template>

<script>
export default {
	props: {
		params: {
			type: Array,
			default: () => []
		}
	}
};
</script>

<style>
.k-labs-docs-params li {
	list-style: square;
	margin-inline-start: var(--spacing-3);
}
.k-labs-docs-params .k-lab-docs-types {
	margin-inline: 1ch;
}
</style>
