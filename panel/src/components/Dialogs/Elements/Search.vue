<template>
	<k-input
		:autofocus="autofocus"
		:placeholder="placeholder"
		:spellcheck="false"
		:value="value"
		class="k-dialog-search"
		icon="search"
		type="text"
		@input="$emit('search', $event)"
	/>
</template>

<script>
/**
 * @displayName DialogSearch
 * @since 4.0.0
 */
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
/** Dialog search field **/
.k-dialog-search {
	margin-bottom: 0.75rem;
	--input-color-border: transparent;
	--input-color-back: var(--color-gray-300);
}
</style>
