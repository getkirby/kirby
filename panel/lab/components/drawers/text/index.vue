<template>
	<k-lab-examples>
		<k-lab-example label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-text-drawer',
						props: {
							icon: 'text',
							title: 'Text',
							text: 'Hello world. This is some nice text.'
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
