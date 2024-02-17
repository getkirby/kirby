<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-date-field-preview value="2023-09-25" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Display">
			<k-lab-table-cell>
				<!-- @code -->
				<k-date-field-preview
					:field="{ display: 'DD.MM.YYYY' }"
					value="2023-09-25"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Invalid date">
			<k-lab-table-cell>
				<!-- @code -->
				<k-date-field-preview value="Not a date" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Time">
			<k-lab-table-cell>
				<!-- @code -->
				<k-date-field-preview
					:field="{ time: { display: 'HH:mm' } }"
					value="2023-09-25 12:12:12"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-date-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
