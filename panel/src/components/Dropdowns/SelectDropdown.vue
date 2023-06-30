<template>
	<k-dropdown class="k-select-dropdown">
		<slot />
		<k-dropdown-content
			ref="dropdown"
			class="k-select-dropdown-content"
			:align="align"
			:navigate="false"
		>
			<k-selector
				ref="selector"
				:options="options"
				@create="$emit('create', $event)"
				@escape="$refs.dropdown.close()"
				@select="$emit('select', $event)"
			/>
		</k-dropdown-content>
	</k-dropdown>
</template>

<script>
export default {
	props: {
		align: String,
		options: Array
	},
	emits: ["create", "select"],
	methods: {
		close() {
			this.$refs.dropdown.close();
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
.k-select-dropdown-content {
	--button-width: 100%;
	width: 15rem;
	overflow: visible;
}
.k-select-dropdown .k-selector-input {
	background: var(--color-gray-800);
	height: var(--height-sm);
}
.k-select-dropdown .k-selector-results {
	margin-top: var(--spacing-2);
}
.k-select-dropdown .k-selector-button {
	gap: 0.75rem;
}
.k-select-dropdown .k-selector-button:hover {
	--button-color-back: var(--dropdown-color-hr);
}
.k-select-dropdown .k-selector-empty {
	color: var(--color-text-dimmed);
}
.k-select-dropdown .k-selector-preview {
	color: var(--color-focus);
	font-weight: var(--font-normal);
}
.k-select-dropdown .k-selector-body + .k-selector-footer {
	padding-top: var(--spacing-2);
	margin-top: var(--spacing-2);
	border-top: 1px solid var(--dropdown-color-hr);
}
</style>
