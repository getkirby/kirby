<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="state" :code="false">
			<k-text>
				<k-code>{{ $panel.drawer.state() }}</k-code>
				<k-button
					text="Open drawer"
					variant="filled"
					@click="
						$panel.drawer.open({
							component: 'k-text-drawer',
							props: {
								icon: 'box',
								text: 'Hello world',
								title: 'Test drawer'
							}
						})
					"
				/>
			</k-text>
		</k-ui-example>

		<h2 class="h4">Methods</h2>

		<k-ui-example label="close" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.close();</k-ui-code>
				<p>Closes the current drawer</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="goTo" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.goTo(id);</k-ui-code>
				<p>Opens one of the drawers in the history by ID</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="input" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.input(value);</k-ui-code>
				<p>Triggers an input event and updates the value prop</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="load" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.load(url);</k-ui-code>
				<p>Loads the state for a drawer by URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="open" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.open(drawer);</k-ui-code>
				<p>Opens a drawer either via object definition or URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="openTab" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.openTab(tabId);</k-ui-code>
				<p>Opens the tab by ID in the current drawer</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="post" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.drawer.post(value, options = {});</k-ui-code
				>
				<p>Sends a post request to the drawer backend route</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="refresh" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.drawer.refresh(options = {});</k-ui-code
				>
				<p>Reloads the drawer props from the backend and updates the state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="reload" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.drawer.reload(options = {});</k-ui-code
				>
				<p>Reloads the drawer props and rerenders the drawer component</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="set" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.set(state);</k-ui-code>
				<p>Overwrites the current drawer state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="state" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.state();</k-ui-code>
				<p>Returns the current drawer state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="submit" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.drawer.submit(value, options = {});</k-ui-code
				>
				<p>Submits the drawer with the given value</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="success" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.drawer.success(success);</k-ui-code
				>
				<p>Handles a success response for the drawer</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="url" :code="false">
			<k-text>
				<k-ui-code language="js">window.panel.drawer.url();</k-ui-code>
				<p>
					Returns the full URL for the drawer, including the path and the URL
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
