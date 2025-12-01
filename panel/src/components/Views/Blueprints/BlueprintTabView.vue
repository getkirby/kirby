<template>
	<k-panel-inside class="k-blueprint-tab-view">
		<k-header>
			{{ label }}
		</k-header>

		<k-stack gap="var(--spacing-12)">
			<k-stack>
				<k-headline>Tab</k-headline>
				<k-definitions>
					<k-definition term="Name">{{ name }}</k-definition>
					<k-definition term="Label">{{ label }}</k-definition>
					<k-definition term="Icon">{{ icon }}</k-definition>
				</k-definitions>
			</k-stack>

			<k-stack>
				<k-headline>Layout</k-headline>
				<k-grid style="gap: var(--spacing-3)">
					<k-column
						v-for="(column, index) in columns"
						:key="'column-' + index"
						:width="column.width"
						class="k-blueprint-layout-column"
					>
						<k-grid style="gap: var(--spacing-3)">
							<k-column
								v-for="field in column.fields"
								:key="'field-' + field.name"
								:width="field.width"
								class="k-blueprint-field-column"
							>
								<k-button
									:link="field.link"
									class="k-blueprint-field"
									variant="filled"
								>
									{{ field.name }}
								</k-button>
							</k-column>
						</k-grid>
					</k-column>
				</k-grid>
			</k-stack>
		</k-stack>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		columns: Array,
		icon: String,
		label: String,
		name: String
	}
};
</script>

<style>
.k-blueprint-layout-column {
	border: 1px dashed var(--color-border);
	border-radius: var(--rounded);
	padding: var(--spacing-6);
	height: 100%;
}
.k-blueprint-field {
	width: 100%;
	justify-content: start;
	font-family: var(--font-mono);
	font-size: var(--text-xs);
	--button-color-back: var(--item-color-back);
}
</style>
