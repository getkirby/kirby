<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-color-field-preview value="rgb(209, 232, 163)" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="With field options">
			<k-lab-table-cell>
				<!-- @code -->
				<k-color-field-preview
					:field="{
						alpha: true,
						format: 'rgb',
						options: [{ text: 'Mint', value: 'rgba(209, 232, 163, .75)' }]
					}"
					value="rgb(209, 232, 163, .75)"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-color-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
