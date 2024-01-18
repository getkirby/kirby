<template>
	<k-lab-examples>
		<k-lab-example label="No fields">
			<k-object-field label="Object" />
		</k-lab-example>

		<k-lab-example label="Default">
			<k-object-field
				:fields="[
					{
						label: 'Name',
						name: 'name',
						type: 'text'
					}
				]"
				label="Object"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
