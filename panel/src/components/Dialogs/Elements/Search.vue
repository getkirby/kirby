<template>
	<k-search-inputbox
		:autofocus="autofocus"
		:placeholder="placeholder"
		:value="value"
		class="k-dialog-search"
		@input="$emit('search', $event)"
	/>
</template>

<script>
export default {
	props: {
		autofocus: {
			default: true,
			type: Boolean
		},
		placeholder: {
			default: () => window.panel.$t("search") + " …",
			type: String
		},
		value: {
			type: String
		}
	},
	emits: ["search"]
};
</script>

<style>
.k-dialog-search {
	margin-bottom: 0.75rem;
}
</style>
