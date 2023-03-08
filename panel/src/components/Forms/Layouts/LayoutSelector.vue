<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		size="medium"
		class="k-layout-selector"
	>
		<k-headline>{{ $t("field.layout.select") }}</k-headline>
		<ul>
			<li
				v-for="(columns, layoutIndex) in layouts"
				:key="layoutIndex"
				:aria-current="isCurrent(layoutIndex)"
				class="k-layout-selector-option"
			>
				<k-grid @click.native="onSelect(columns, layoutIndex)">
					<k-column
						v-for="(column, columnIndex) in columns"
						:key="columnIndex"
						:width="column"
					/>
				</k-grid>
			</li>
		</ul>
	</k-dialog>
</template>

<script>
/**
 * @internal
 */
export default {
	inheritAttrs: false,
	props: {
		layouts: Array
	},
	data() {
		return {
			payload: null
		};
	},
	methods: {
		close() {
			this.$refs.dialog.close();
		},
		isCurrent(layoutIndex) {
			return layoutIndex === this.payload?.layoutIndex;
		},
		onSelect(columns, layoutIndex) {
			this.$emit("select", columns, layoutIndex, this.payload);
		},
		open(payload) {
			this.payload = payload;
			this.$refs.dialog.open();
		}
	}
};
</script>

<style>
.k-layout-selector.k-dialog {
	background: var(--color-dark);
	color: var(--color-white);
}
.k-layout-selector .k-headline {
	line-height: 1;
	margin-top: -0.25rem;
	margin-bottom: 1.5rem;
}

.k-layout-selector ul {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	grid-gap: 1.5rem;
}

.k-layout-selector-option {
	outline: 2px solid var(--option-outline, transparent);
	outline-offset: 2px;
}
.k-layout-selector-option[aria-current="true"] {
	--option-outline: var(--color-focus);
}
.k-layout-selector-option:not([aria-current]):hover {
	--option-outline: var(--color-green-400);
}
.k-layout-selector-option:last-child {
	margin-bottom: 0;
}

.k-layout-selector-option .k-grid {
	gap: 2px;
	grid-template-columns: repeat(var(--columns), 1fr);
	box-shadow: var(--shadow);
	cursor: pointer;
}
.k-layout-selector-option .k-column {
	grid-column: span var(--span);
	background: var(--color-slate-800);
	height: 5rem;
}
</style>
