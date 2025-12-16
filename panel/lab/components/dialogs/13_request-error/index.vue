<template>
	<k-lab-examples>
		<k-lab-example label="Request Error">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-request-error-dialog',
						props: {
							message: 'The request failed',
							request: {
								url: '/example/request',
								method: 'POST'
							},
							response: {
								status: 400
							},
							exception: {
								file: '/site/templates/default.php',
								line: 22,
								type: 'Kirby\\Exception\\InvalidArgumentException',
								url: 'vscode://'
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>

		<k-lab-example label="Request Error with details">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-request-error-dialog',
						props: {
							message: 'The request failed',
							request: {
								url: '/example/request',
								method: 'POST'
							},
							response: {
								status: 400
							},
							exception: {
								file: '/site/templates/default.php',
								line: 22,
								type: 'Kirby\\Exception\\InvalidArgumentException',
								url: 'vscode://'
							},
							details: {
								foo: 'bar'
							}
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>

		<k-lab-example label="Request Error with details and PHP Trace">
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-request-error-dialog',
						props: {
							message: 'The request failed',
							request: {
								url: '/example/request',
								method: 'POST'
							},
							response: {
								status: 400
							},
							exception: {
								file: '/site/templates/default.php',
								line: 22,
								type: 'Kirby\\Exception\\InvalidArgumentException',
								url: 'vscode://'
							},
							details: {
								foo: 'bar'
							},
							trace: [
								{
									file: '{kirby}/src/Cms/Page.php',
									line: 22,
									class: 'Kirby\\Cms\\Page',
									type: '->',
									function: 'update'
								},
								{
									file: '{kirby}/src/Cms/Page.php',
									line: 22,
									class: 'Kirby\\Cms\\Page',
									type: '->',
									function: 'update'
								}
							]
						}
					})
				"
			>
				Open
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
