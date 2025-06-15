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
 * @unstable
 */
export default {
	inheritAttrs: false,
	props: {
		current: [Boolean, String],
		disabled: Boolean,
		download: Boolean,
		icon: String,
		link: String,
		target: String
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
}
.k-dropdown-item.k-button:focus {
	outline: var(--outline);
}
.k-dropdown-item.k-button[aria-current="true"] {
	--button-color-text: var(--dropdown-color-current);
}
.k-dropdown-item.k-button[aria-current="true"]::after {
	margin-inline-start: auto;
	text-align: center;
	content: "✓";
	padding-inline-start: var(--spacing-1);
}
.k-dropdown-item.k-button:not([aria-disabled="true"]):hover {
	--button-color-back: var(--dropdown-color-hr);
}
</style>
