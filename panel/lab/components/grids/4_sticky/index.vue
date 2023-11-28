<template>
	<k-lab-examples>
		<k-lab-example label="Sticky column">
			<k-grid variant="columns">
				<k-column width="2/3" style="height: 2000px">
					This is not sticky
				</k-column>
				<k-column :sticky="true" width="1/3">This is sticky</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-example .k-column {
	background: var(--color-gray-250);
	border-radius: var(--rounded);
	padding: var(--spacing-3);
}
</style>
