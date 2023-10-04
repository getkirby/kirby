<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-remove-dialog',
						props: {
							text: 'Do you really want to remove this?',
						},
					})
				"
			>
				Open
			</k-button>
		</k-ui-example>
	</k-ui-examples>
</template>
