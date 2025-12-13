<template>
	<k-lab-examples>
		<k-lab-example label="Validation Issues">
			<k-validation-issues
				:fields="{
					title: {
						label: 'Title',
						issues: ['The field is rquired']
					},
					text: {
						label: 'Text',
						issues: [
							'The text must not be shorter than 10 characters',
							'The text must not be longer than 140 characters'
						]
					},
					email: {
						label: 'Email',
						issues: ['Enter a valid email address']
					}
				}"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
