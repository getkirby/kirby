<template>
	<k-lab-examples>
		<k-lab-example label="Listed">
			<k-lab-table-cell>
				<!-- @code -->
				<k-flag-field-preview :value="{ status: 'listed' }" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Unlisted">
			<k-lab-table-cell>
				<!-- @code -->
				<k-flag-field-preview :value="{ status: 'unlisted' }" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Draft">
			<k-lab-table-cell>
				<!-- @code -->
				<k-flag-field-preview :value="{ status: 'draft' }" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-flag-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
