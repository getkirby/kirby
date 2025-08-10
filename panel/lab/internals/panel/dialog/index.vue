<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="state" :code="false">
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
		</k-lab-example>

		<h2 class="h4">Methods</h2>

		<k-lab-example label="close" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.close();</k-code>
				<p>Closes the current dialog</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="goTo" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.goTo(id);</k-code>
				<p>Opens one of the dialogs in the history by ID</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="input" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.input(value);</k-code>
				<p>Triggers an input event and updates the value prop</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="load" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.load(url);</k-code>
				<p>Loads the state for a dialog by URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.open(dialog);</k-code>
				<p>Opens a dialog either via object definition or URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="post" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.dialog.post(value, options = {});</k-code
				>
				<p>Sends a post request to the dialog backend route</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="refresh" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.dialog.refresh(options = {});</k-code
				>
				<p>Reloads the dialog props from the backend and updates the state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="reload" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.reload(options = {});</k-code>
				<p>Reloads the dialog props and rerenders the dialog component</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="set" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.set(state);</k-code>
				<p>Overwrites the current dialog state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="state" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.state();</k-code>
				<p>Returns the current dialog state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="submit" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.dialog.submit(value, options = {});</k-code
				>
				<p>Submits the dialog with the given value</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="success" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.success(success);</k-code>
				<p>Handles a success response for the dialog</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="url" :code="false">
			<k-text>
				<k-code language="js">window.panel.dialog.url();</k-code>
				<p>
					Returns the full URL for the dialog, including the path and the URL
					query
				</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
