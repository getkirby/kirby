<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-tags-field-preview
					:value="[
						{
							text: 'A'
						},
						{
							text: 'B'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Overflow: many items">
			<k-lab-table-cell style="max-width: 20rem">
				<!-- @code -->
				<k-tags-field-preview
					:value="[
						'Design',
						'Development',
						'Photography',
						'Architecture',
						'Travel',
						'Food',
						'Music',
						'Art',
						'Nature',
						'Sports',
						'Fashion',
						'Science'
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Overflow: long text">
			<k-lab-table-cell style="max-width: 20rem">
				<!-- @code -->
				<k-tags-field-preview
					:value="[
						{
							text: 'Aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
						},
						{
							text: 'Bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-tags-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
