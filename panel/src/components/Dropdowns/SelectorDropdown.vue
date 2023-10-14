<template>
	<div class="k-selector-dropdown">
		<slot />
		<k-dropdown-content
			ref="dropdown"
			class="k-selector-dropdown-content"
			:align-x="align"
			:autofocus="false"
			:disabled="disabled"
			:navigate="false"
			@close="reset"
		>
			<k-selector
				ref="selector"
				v-bind="$props"
				@create="create"
				@escape="$refs.dropdown.close()"
				@select="select"
			/>
		</k-dropdown-content>
	</div>
</template>

<script>
import { props as SelectorProps } from "@/components/Forms/Selector.vue";

/**
 * @since 4.0.0
 */
export default {
	mixins: [SelectorProps],
	props: {
		align: String,
		disabled: Boolean
	},
	emits: ["create", "select"],
	methods: {
		close() {
			this.$refs.dropdown.close();
		},
		create(value) {
			this.$emit("create", value);
			this.close();
		},
		open(opener) {
			this.$refs.dropdown.open(opener);
		},
		reset() {
			this.$refs.selector.reset();
		},
		select(value) {
			this.$emit("select", value);
			this.close();
		},
		toggle(opener) {
			this.$refs.dropdown.toggle(opener);
		}
	}
};
</script>

<style>
.k-selector-dropdown-content {
	--color-text-dimmed: var(--color-gray-400);
	min-width: 15rem;
	max-width: 30rem;
	padding: 0;
}
.k-selector-dropdown .k-selector-header {
	border-bottom: 1px solid var(--dropdown-color-hr);
}
.k-selector-dropdown .k-selector-label {
	padding-inline: var(--spacing-3);
	height: var(--height-lg);
	display: flex;
	font-weight: var(--font-semi);
	align-items: center;
}
.k-selector-dropdown .k-selector-search {
	padding: var(--dropdown-padding);
	border-top: 1px solid var(--dropdown-color-hr);
}
.k-selector-dropdown .k-selector-input {
	background: var(--color-gray-800);
}
.k-selector-dropdown .k-selector-footer {
	padding: var(--dropdown-padding);
}
.k-selector-dropdown .k-selector-body {
	max-height: calc(
		(var(--button-height) * 10) + calc(var(--dropdown-padding) * 2)
	);
	overflow-y: auto;
	padding: var(--dropdown-padding);
	overscroll-behavior: contain;
	scroll-padding-top: var(--dropdown-padding);
	scroll-padding-bottom: var(--dropdown-padding);
}
.k-selector-dropdown .k-selector-button {
	gap: 0.75rem;
}
.k-selector-dropdown .k-selector-button:hover {
	--button-color-back: var(--dropdown-color-hr);
}

.k-selector-dropdown .k-selector-body + .k-selector-footer {
	border-top: 1px solid var(--dropdown-color-hr);
}
</style>
