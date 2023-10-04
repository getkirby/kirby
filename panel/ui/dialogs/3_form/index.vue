<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="default">
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
		</k-ui-example>
	</k-ui-examples>
</template>
