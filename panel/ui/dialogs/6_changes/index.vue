<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="$panel.dialog.open('changes')"
			>
				Open
			</k-button>
		</k-ui-example>
	</k-ui-examples>
</template>
