<template>
	<k-ui-examples>
		<k-ui-example label="default">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-form-drawer',
						props: {
							icon: 'box',
							title: 'Form',
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
		<k-ui-example label="tabs">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-form-drawer',
						props: {
							icon: 'box',
							title: 'Form',
							tabs: {
								content: {
									name: 'content',
									label: 'Content',
									fields: {
										text: {
											label: 'Text',
											type: 'textarea',
											size: 'large',
										},
									},
								},
								settings: {
									name: 'settings',
									label: 'Settings',
									fields: {
										date: {
											label: 'Date',
											type: 'date',
										},
										public: {
											label: 'Public',
											type: 'toggle',
										},
									},
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
