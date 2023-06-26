<template>
	<k-button
		ref="button"
		v-bind="$props"
		class="k-dropdown-item"
		@click="onClick"
	>
		<!-- @slot The item's content/text -->
		<slot />
	</k-button>
</template>

<script>
/**
 * Item to be used within `<k-dropdown-content>`
 * @example <k-dropdown-item>Option A</k-dropdown-item>
 * @internal
 */
export default {
	inheritAttrs: false,
	props: {
		current: [Boolean, String],
		disabled: Boolean,
		icon: String,
		image: [String, Object],
		link: String,
		target: String,
		theme: String,
		upload: String
	},
	emit: ["click"],
	methods: {
		focus() {
			this.$refs.button.focus();
		},
		onClick(event) {
			this.$parent.close();
			this.$emit("click", event);
		},
		tab() {
			this.$refs.button.tab();
		}
	}
};
</script>

<style>
.k-dropdown-item.k-button {
	--button-height: var(--height-sm);
	--button-color-text: var(--dropdown-color-text);
	display: flex;
	justify-content: flex-start;
	gap: 0.75rem;
	border-radius: var(--rounded-sm);
	width: 100%;
}
.k-dropdown-item.k-button[aria-current] {
	--button-color-text: var(--color-blue-500);
}
.k-dropdown-item.k-button[aria-disabled] {
	opacity: var(--opacity-disabled);
}
.k-dropdown-item.k-button + .k-dropdown-item.k-button {
	margin-top: 2px;
}
</style>
