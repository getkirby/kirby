<template>
	<k-lab-examples>
		<k-lab-example label="default">
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
									type: 'text'
								},
								email: {
									label: 'Email',
									type: 'email'
								}
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example label="tabs">
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
											size: 'large'
										}
									}
								},
								settings: {
									name: 'settings',
									label: 'Settings',
									fields: {
										date: {
											label: 'Date',
											type: 'date'
										},
										public: {
											label: 'Public',
											type: 'toggle'
										}
									}
								}
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example label="lots of tabs">
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
									fields: {}
								},
								settings: {
									name: 'settings',
									label: 'Settings',
									fields: {}
								},
								seo: {
									name: 'seo',
									label: 'SEO',
									fields: {}
								},
								validation: {
									name: 'validation',
									label: 'Validation',
									fields: {}
								},
								help: {
									name: 'help',
									label: 'Help',
									fields: {}
								},
								theme: {
									name: 'theme',
									label: 'Theme',
									fields: {}
								},
								misc: {
									name: 'misc',
									label: 'Misc',
									fields: {}
								}
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
