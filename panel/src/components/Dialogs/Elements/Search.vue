<template>
	<k-input
		:autofocus="autofocus"
		:placeholder="placeholder"
		:value="value"
		class="k-dialog-search"
		icon="search"
		type="text"
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
	}
};
</script>

<style>
/** Dialog search field **/
.k-dialog-search {
	margin-bottom: 0.75rem;
}
.k-dialog-search.k-input {
	background: rgba(0, 0, 0, 0.075);
	padding: 0 1rem;
	height: 36px;
	border-radius: var(--rounded);
}
</style>
