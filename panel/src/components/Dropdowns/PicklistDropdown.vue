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
			@click.native.stop
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
	emits: ["create", "input"],
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
		open(opener) {
			this.$refs.dropdown.open(opener);
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

	padding: 0;
	max-width: 30rem;
	min-width: 8rem;
}

.k-picklist-dropdown
	:where(
		.k-picklist-input-header,
		.k-picklist-input-body,
		.k-picklist-input-footer
	) {
	padding: var(--dropdown-padding);
}

.k-picklist-dropdown .k-picklist-input-header {
	border-bottom: 1px solid var(--dropdown-color-hr);
}
.k-picklist-dropdown .k-picklist-input-search {
	background: var(--dropdown-color-hr);
	padding-inline-end: var(--input-padding);
}
.k-picklist-dropdown .k-picklist-input-create {
	--button-rounded: 1rem;
	--button-height: 1.125rem;
}
.k-picklist-dropdown .k-picklist-input-create:focus {
	--button-color-back: var(--color-blue-500);
	--button-color-text: var(--color-black);
}

.k-picklist-dropdown .k-picklist-input-body {
	/* 2px = grid gap of choices list */
	max-height: calc(
		var(--button-height) * 9.5 + 2px * 9 + var(--dropdown-padding)
	);
	overflow-y: auto;
	outline-offset: -2px;
	overscroll-behavior: contain;
	scroll-padding-top: var(--dropdown-padding);
	scroll-padding-bottom: var(--dropdown-padding);
}

.k-picklist-dropdown .k-picklist-input-options .k-choice-input {
	--choice-color-border: var(--dropdown-color-hr);
	--choice-color-back: var(--dropdown-color-hr);
	--choice-color-info: var(--color-text-dimmed);

	min-height: var(--button-height);
	border-radius: var(--picklist-rounded);
	padding-block: 0.375rem;
}
.k-picklist-dropdown .k-picklist-input-options .k-choice-input-label {
	flex-grow: 1;
}
.k-picklist-dropdown .k-picklist-input-options li + li {
	margin-top: 0;
}
.k-picklist-dropdown
	.k-picklist-input-options
	.k-choice-input[aria-disabled="true"]
	input {
	--choice-color-border: var(--dropdown-color-hr);
	--choice-color-back: var(--dropdown-color-hr);
	--choice-color-checked: var(--dropdown-color-hr);
	opacity: var(--opacity-disabled);
}
.k-picklist-dropdown
	.k-picklist-input-options
	.k-choice-input:not([aria-disabled="true"]):hover {
	background-color: var(--dropdown-color-hr);

	--choice-color-border: var(--dropdown-color-bg);
}
.k-picklist-dropdown
	.k-picklist-input-options
	.k-choice-input:not([aria-disabled="true"]):focus-within {
	--choice-color-text: var(--color-blue-500);
}

.k-picklist-dropdown .k-picklist-input-more.k-button:hover {
	--button-color-back: var(--dropdown-color-hr);
}

.k-picklist-dropdown .k-picklist-input-body + .k-picklist-input-footer {
	border-top: 1px solid var(--dropdown-color-hr);
}
</style>
