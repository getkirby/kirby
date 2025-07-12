<template>
	<k-dialog
		v-bind="$props"
		:class="['k-layout-selector', $attrs.class]"
		:size="selector?.size ?? 'medium'"
		:style="$attrs.style"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
	>
		<h3 class="k-label">{{ label }}</h3>
		<k-navigate
			:style="{ '--columns': Number(selector?.columns ?? 3) }"
			axis="x"
			class="k-layout-selector-options"
		>
			<button
				v-for="(columns, layoutIndex) in layouts"
				:key="layoutIndex"
				:aria-current="value === columns"
				:aria-label="columns.join(',')"
				:value="columns"
				class="k-layout-selector-option"
				@click="$emit('input', columns)"
			>
				<k-grid aria-hidden>
					<k-column
						v-for="(column, columnIndex) in columns"
						:key="columnIndex"
						:width="column"
					/>
				</k-grid>
			</button>
		</k-navigate>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * @unstable
 */
export default {
	mixins: [Dialog],
	inheritAttrs: false,
	props: {
		// eslint-disable-next-line vue/require-prop-types
		cancelButton: {
			default: false
		},
		label: {
			default: () => window.panel.t("field.layout.select"),
			type: String
		},
		layouts: {
			type: Array
		},
		selector: Object,
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: false
		},
		value: {
			type: Array
		}
	},
	emits: ["cancel", "input", "submit"]
};
</script>

<style>
.k-layout-selector h3 {
	margin-top: -0.5rem;
	margin-bottom: var(--spacing-3);
}
.k-layout-selector-options {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: var(--spacing-6);
}
@media screen and (min-width: 65em) {
	.k-layout-selector-options {
		grid-template-columns: repeat(var(--columns), 1fr);
	}
}
.k-layout-selector-option {
	--color-border: light-dark(
		hsla(var(--color-gray-hs), 0%, 6%),
		hsla(var(--color-gray-hs), 60%, 10%)
	);
	--color-back: light-dark(var(--color-white), var(--color-gray-950));
	border-radius: var(--rounded);
}
.k-layout-selector-option:focus-visible {
	outline: var(--outline);
	outline-offset: -1px;
}
.k-layout-selector-option .k-grid {
	border: 1px solid var(--color-border);
	gap: 1px;
	grid-template-columns: repeat(var(--columns), 1fr);
	cursor: pointer;
	background: var(--color-border);
	border-radius: var(--rounded);
	overflow: hidden;
	box-shadow: var(--shadow);
	height: 5rem;
}
.k-layout-selector-option .k-column {
	grid-column: span var(--span);
	background: var(--color-back);
	height: 100%;
}
.k-layout-selector-option:hover {
	--color-border: light-dark(var(--color-gray-500), var(--color-gray-800));
	--color-back: light-dark(var(--color-gray-100), var(--color-gray-900));
}
.k-layout-selector-option[aria-current="true"] {
	--color-border: var(--color-focus);
	--color-back: var(--color-blue-300);
}
</style>
