<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Drawer'
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Icon">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							icon: 'box',
							title: 'Drawer'
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Sizes">
			<k-button
				v-for="size in ['tiny', 'small', 'default', 'large', 'huge']"
				:key="size"
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							size,
							title: 'Drawer'
						}
					})
				"
			>
				{{ size }}
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Nested">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Parent drawer',
							submitButton: 'Open nested'
						},
						on: {
							submit: (event) => {
								$panel.drawer.open({
									component: 'k-drawer',
									props: {
										title: 'Nested drawer',
										submitButton: 'Submit & close'
									}
								});
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
