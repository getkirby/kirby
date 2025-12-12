<template>
	<k-lab-examples>
		<k-lab-example label="Validation Issues">
			<k-validation-issues
				:issues="[
					{
						field: 'Title',
						issues: ['The field is rquired']
					},
					{
						field: 'Text',
						issues: [
							'The text must not be shorter than 10 characters',
							'The text must not be longer than 140 characters'
						]
					},
					{
						field: 'Email',
						issues: ['Enter a valid email address']
					}
				]"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
