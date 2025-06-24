<template>
	<k-input
		:autofocus="autofocus"
		:placeholder="placeholder"
		:value="value"
		class="k-dialog-search"
		icon="search"
		type="search"
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
	--input-color-back: light-dark(var(--color-gray-300), var(--color-gray-950));
}
</style>
