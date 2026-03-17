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
		<k-lab-example :flex="true" label="Sizes">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Tiny drawer',
							size: 'tiny'
						}
					})
				"
			>
				Tiny
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Small drawer',
							size: 'small'
						}
					})
				"
			>
				Small
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Default drawer',
							size: 'default'
						}
					})
				"
			>
				Default
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Large drawer',
							size: 'large'
						}
					})
				"
			>
				Large
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-drawer',
						props: {
							title: 'Huge drawer',
							size: 'huge'
						}
					})
				"
			>
				Huge
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
