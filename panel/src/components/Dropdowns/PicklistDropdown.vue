<template>
	<k-dropdown-content
		ref="dropdown"
		align-x="start"
		:disabled="disabled"
		:navigate="false"
		class="k-picklist-dropdown"
		@click.native.stop
	>
		<k-picklist-input
			v-bind="$props"
			@create="add"
			@input="input"
			@escape="$refs.dropdown.close()"
		/>
	</k-dropdown-content>
</template>

<script>
import { props as PicklistInputProps } from "@/components/Forms/Input/PicklistInput.vue";

/**
 * @since 4.0.0
 */
export default {
	mixins: [PicklistInputProps],
	methods: {
		close() {
			this.$refs.dropdown.close();
		},
		add(input) {
			/**
			 * Create new from input
			 * @property {string} input
			 */
			this.$emit("create", input);
		},
		input(values) {
			/**
			 * Updated values
			 * @property {array} values
			 */
			this.$emit("input", values);
		},
		open() {
			this.$refs.dropdown.open();
		},
		toggle() {
			this.$refs.dropdown.toggle();
		}
	}
};
</script>

<style>
.k-picklist-dropdown {
	--color-text-dimmed: var(--color-gray-400);
	--picklist-separator: var(--dropdown-color-hr);

	padding: 0;
	min-width: 15rem;
	max-width: 30rem;
}

.k-picklist-dropdown
	:where(
		.k-picklist-input-header,
		.k-picklist-input-options,
		.k-picklist-input-empty,
		.k-picklist-input-footer
	) {
	padding: var(--dropdown-padding);
}
.k-picklist-dropdown .k-picklist-input-search {
	background: var(--color-gray-800);
}

.k-picklist-dropdown .k-picklist-input-options {
	/* 2px = grid gap of choices list */
	max-height: calc(
		var(--button-height) * 9.5 + 2px * 9 + var(--dropdown-padding)
	);
	overflow-y: auto;
	overscroll-behavior: contain;
	scroll-padding-top: var(--dropdown-padding);
	scroll-padding-bottom: var(--dropdown-padding);
}
.k-picklist-dropdown .k-picklist-input-options .k-choice-input:hover {
	background-color: var(--dropdown-color-hr);
}
.k-picklist-dropdown .k-picklist-input-more.k-button:hover {
	--button-color-back: var(--dropdown-color-hr);
}

.k-picklist-dropdown .k-picklist-input-create:hover {
	--button-color-back: var(--dropdown-color-hr);
}
</style>
