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
				:data-disabled="layoutIndex === payload?.layoutIndex"
				class="k-layout-selector-option"
			>
				<k-grid @click.native="$emit('select', columns, layoutIndex, payload)">
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
		open(payload) {
			this.payload = payload;
			this.$refs.dialog.open();
		}
	}
};
</script>

<style>
.k-layout-selector.k-dialog {
	background: #313740;
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
.k-layout-selector-option .k-grid {
	height: 5rem;
	grid-gap: 2px;
	box-shadow: var(--shadow);
	cursor: pointer;
}
.k-layout-selector-option:hover {
	outline: 2px solid var(--color-green-300);
	outline-offset: 2px;
}
.k-layout-selector-option:last-child {
	margin-bottom: 0;
}
.k-layout-selector-option .k-column {
	display: flex;
	background: rgba(255, 255, 255, 0.2);
	justify-content: center;
	font-size: var(--text-xs);
	align-items: center;
}
.k-layout-selector-option[data-disabled="true"] {
	cursor: not-allowed;
	opacity: 0.25;
}
.k-layout-selector-option[data-disabled="true"] * {
	pointer-events: none;
}
</style>
