<template>
	<k-dropdown class="k-select-dropdown">
		<slot />
		<k-dropdown-content
			ref="dropdown"
			class="k-select-dropdown-content"
			:align="align"
			:autofocus="false"
			:navigate="false"
			@close="reset"
		>
			<k-selector
				ref="selector"
				v-bind="$props"
				@create="$emit('create', $event)"
				@escape="$refs.dropdown.close()"
				@select="$emit('select', $event)"
			/>
		</k-dropdown-content>
	</k-dropdown>
</template>

<script>
import { props as Selector } from "@/components/Forms/Selector.vue";

export default {
	mixins: [Selector],
	props: {
		align: String
	},
	emits: ["create", "select"],
	methods: {
		close() {
			this.$refs.dropdown.close();
		},
		open() {
			this.$refs.dropdown.open();
		},
		reset() {
			this.$refs.selector.reset();
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
	padding: 0;
}

.k-select-dropdown .k-selector-input {
	background: var(--dropdown-color-hr);
	height: var(--height-sm);
	line-height: var(--height-sm);
}
.k-select-dropdown .k-selector-input::placeholder {
	color: var(--color-gray-400);
}
.k-select-dropdown .k-selector-header {
	border-bottom: 1px solid var(--dropdown-color-hr);
}
.k-select-dropdown .k-selector-header,
.k-select-dropdown .k-selector-footer {
	padding: var(--dropdown-padding);
}
.k-select-dropdown .k-selector-body {
	max-height: calc((var(--height-sm) * 10) + var(--spacing-2));
	overflow-y: auto;
	padding-block: var(--spacing-1);
	padding-inline: var(--dropdown-padding);
	overscroll-behavior: contain;
	scroll-padding-top: var(--spacing-1);
	scroll-padding-bottom: var(--spacing-1);
}
.k-select-dropdown .k-selector-button {
	gap: 0.75rem;
	--button-rounded: var(--rounded-sm);
}
.k-select-dropdown .k-selector-button:hover {
	--button-color-back: var(--dropdown-color-hr);
}
.k-select-dropdown .k-selector-empty {
	color: var(--color-text-dimmed);
}

.k-select-dropdown .k-selector-footer {
	border-top: 1px solid var(--dropdown-color-hr);
}
</style>
