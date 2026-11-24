<template>
	<k-lab-examples>
		<k-lab-example label="Validation Error">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-validation-error-dialog',
						props: {
							message: 'Your changes could not be saved',
							fields: {
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
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
