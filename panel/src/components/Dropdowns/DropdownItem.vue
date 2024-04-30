<template>
	<k-button
		ref="button"
		v-bind="{
			current,
			dialog,
			drawer,
			disabled,
			icon,
			link,
			target,
			theme
		}"
		class="k-dropdown-item"
		@click="onClick"
	>
		<!-- @slot The item's content/text -->
		<slot />
	</k-button>
</template>

<script>
import { props as ButtonProps } from "@/components/Navigation/Button.vue";

/**
 * Item to be used within `<k-dropdown-content>`
 * @example <k-dropdown-item>Option A</k-dropdown-item>
 * @internal
 */
export default {
	mixins: [ButtonProps],
	inheritAttrs: false,
	props: {
		// unset unnecessary props
		autofocus: null,
		click: null,
		dropdown: null,
		element: null,
		responsive: null,
		role: null,
		selected: null,
		size: null,
		type: null,
		variant: null
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
.k-dropdown-item.k-button[aria-current] {
	--button-color-text: var(--color-blue-500);
}
.k-dropdown-item.k-button:not([aria-disabled]):hover {
	--button-color-back: var(--dropdown-color-hr);
}
</style>
