<template>
	<k-button
		ref="button"
		v-bind="$props"
		:class="['k-dropdown-item', $attrs.class]"
		:style="$attrs.style"
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
	--button-align: flex-start;
	--button-color-text: var(--dropdown-color-text);
	--button-height: var(--height-sm);
	--button-rounded: var(--rounded-sm);
	--button-width: 100%;
	display: flex;
	gap: 0.75rem;
}
.k-dropdown-item.k-button:focus {
	outline: var(--outline);
}
.k-dropdown-item.k-button[aria-current="true"] {
	--button-color-text: var(--color-blue-500);
}
.k-dropdown-item.k-button:not([aria-disabled="true"]):hover {
	--button-color-back: var(--dropdown-color-hr);
}
</style>
