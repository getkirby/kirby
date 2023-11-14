<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-license-dialog',
						props: {
							license: {
								type: 'Kirby Enterprise',
								code: 'K-ENT-123456789',
								purchased: '2023-12-01',
								activated: '2023-12-01'
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
