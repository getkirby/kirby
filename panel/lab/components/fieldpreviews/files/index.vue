<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-files-field-preview
					:value="[
						{
							image: { icon: 'code', back: 'black', color: 'white' },
							filename: 'test-1.js',
							link: '/'
						},
						{
							image: { icon: 'code', back: 'black', color: 'white' },
							filename: 'test-2.js',
							link: '/'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Overflow">
			<k-lab-table-cell>
				<!-- @code -->
				<k-files-field-preview
					:value="
						Array(20).fill({
							image: { icon: 'code', back: 'black', color: 'white' },
							filename: 'test-1.js',
							link: '/'
						})
					"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-files-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="HTML">
			<k-lab-table-cell>
				<!-- @code -->
				<k-files-field-preview
					:value="[
						{
							image: { icon: 'code', back: 'black', color: 'white' },
							filename: '<b>test-1.js</b> (123kb)',
							link: '/'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
