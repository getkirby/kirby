<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="state" :code="false">
			<k-text>
				<k-code>{{ $panel.dialog.state() }}</k-code>
				<k-button
					text="Open dialog"
					variant="filled"
					@click="
						$panel.dialog.open({
							component: 'k-text-dialog',
							props: {
								text: 'Hello world'
							}
						})
					"
				/>
			</k-text>
		</k-ui-example>

		<h2 class="h4">Methods</h2>

		<k-ui-example label="close" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.close();</k-ui-code>
				<p>Closes the current dialog</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="input" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.input(value);</k-ui-code>
				<p>Triggers an input event and updates the value prop</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="load" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.load(url);</k-ui-code>
				<p>Loads the state for a dialog by URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="open" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.open(dialog);</k-ui-code>
				<p>Opens a dialog either via object definition or URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="post" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dialog.post(value, options = {});</k-ui-code
				>
				<p>Sends a post request to the dialog backend route</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="refresh" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dialog.refresh(options = {});</k-ui-code
				>
				<p>Reloads the dialog props from the backend and updates the state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="reload" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dialog.reload(options = {});</k-ui-code
				>
				<p>Reloads the dialog props and rerenders the dialog component</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="set" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.set(state);</k-ui-code>
				<p>Overwrites the current dialog state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="state" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.state();</k-ui-code>
				<p>Returns the current dialog state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="submit" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dialog.submit(value, options = {});</k-ui-code
				>
				<p>Submits the dialog with the given value</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="success" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dialog.success(success);</k-ui-code
				>
				<p>Handles a success response for the dialog</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="url" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.dialog.url();</k-ui-code>
				<p>
					Returns the full URL for the dialog, including the path and the URL
					query
				</p>
			</k-text>
		</k-ui-example>
	</k-ui-examples>
</template>

<style>
.k-ui-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-ui-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
