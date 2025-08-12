<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-form-dialog',
						props: {
							fields: {
								name: {
									label: 'Name',
									type: 'text',
								},
								email: {
									label: 'Email',
									type: 'email',
								},
							},
						},
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
