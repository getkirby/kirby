<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-users-field-preview
					:value="[
						{
							image: { icon: 'user', back: 'black', color: 'white' },
							email: 'marge@getkirby.com',
							link: '/'
						},
						{
							image: { icon: 'user', back: 'black', color: 'white' },
							email: 'maggie@getkirby.com',
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
				<k-users-field-preview
					:value="
						Array(20).fill({
							image: { icon: 'user', back: 'black', color: 'white' },
							email: 'marge@getkirby.com',
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
				<k-users-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="HTML">
			<k-lab-table-cell>
				<!-- @code -->
				<k-users-field-preview
					:value="[
						{
							image: { icon: 'user', back: 'black', color: 'white' },
							email: '<b>marge@getkirby.com</b> (Marge Simpson)',
							link: '/'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>
