<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
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
		</k-lab-example>
	</k-lab-examples>
</template>
