<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-dialog'
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Sizes">
			<template v-for="size in ['small', 'medium', 'large', 'huge']">
				<k-button
					:key="size"
					icon="open"
					variant="filled"
					@click="
						$panel.dialog.open({
							component: 'k-dialog',
							props: {
								size: size
							}
						})
					"
				>
					{{ size }}
				</k-button>
			</template>
		</k-lab-example>
		<k-lab-example :flex="true" label="Custom Buttons">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-dialog',
						props: {
							submitButton: {
								icon: 'heart',
								text: 'Like',
								theme: 'love'
							}
						}
					})
				"
			>
				Custom Submit Button
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-dialog',
						props: {
							cancelButton: {
								icon: 'heart',
								text: 'Like',
								theme: 'love'
							}
						}
					})
				"
			>
				Custom Cancel Button
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-dialog',
						props: {
							submitButton: false
						}
					})
				"
			>
				Removed Submit Button
			</k-button>
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-dialog',
						props: {
							cancelButton: false
						}
					})
				"
			>
				Removed Cancel Button
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="Disabled">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-dialog',
						props: {
							disabled: true
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
					$panel.dialog.open({
						component: 'k-text-dialog',
						props: {
							text: 'Parent dialog',
							submitButton: 'Open nested'
						},
						on: {
							submit: (event) => {
								$panel.dialog.open({
									component: 'k-text-dialog',
									props: {
										text: 'Nested dialog',
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
