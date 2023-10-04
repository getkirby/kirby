<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-text-dialog',
						props: {
							text: 'This is a text dialog',
						},
					})
				"
			>
				Open
			</k-button>
		</k-ui-example>
	</k-ui-examples>
</template>
