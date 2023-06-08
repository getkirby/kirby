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
		disabled: Boolean,
		icon: String,
		image: [String, Object],
		link: String,
		target: String,
		theme: String,
		upload: String,
		current: [String, Boolean]
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
