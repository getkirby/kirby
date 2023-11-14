<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="active">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-license-dialog',
						props: {
							cancelButton: false,
							submitButton: false,
							license: {
								type: 'Kirby Enterprise',
								code: 'K-ENT-123456789',
								icon: 'check',
								info: $t('license.status.active.info', { date: '2026-12-12' }),
								renewal: '2026-12-01',
								theme: 'positive'
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="inactive">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-license-dialog',
						props: {
							submitButton: {
								icon: 'cart',
								text: 'Renew',
								theme: 'love'
							},
							license: {
								type: 'Kirby Enterprise',
								code: 'K-ENT-123456789',
								icon: 'clock',
								info: $t('license.status.inactive.info', {
									date: '2021-12-12'
								}),
								renewal: '2023-10-01',
								theme: 'warning'
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="legacy">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-license-dialog',
						props: {
							submitButton: {
								icon: 'cart',
								text: 'Renew',
								theme: 'love'
							},
							license: {
								type: 'Kirby 3',
								code: 'K3-PRO-123456789',
								icon: 'alert',
								info: $t('license.status.legacy.info'),
								theme: 'love'
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
