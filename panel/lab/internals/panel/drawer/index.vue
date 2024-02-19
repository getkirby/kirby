<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="state" :code="false">
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
		</k-lab-example>

		<h2 class="h4">Methods</h2>

		<k-lab-example label="close" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.close();</k-code>
				<p>Closes the current drawer</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="goTo" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.goTo(id);</k-code>
				<p>Opens one of the drawers in the history by ID</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="input" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.input(value);</k-code>
				<p>Triggers an input event and updates the value prop</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="load" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.load(url);</k-code>
				<p>Loads the state for a drawer by URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.open(drawer);</k-code>
				<p>Opens a drawer either via object definition or URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="openTab" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.openTab(tabId);</k-code>
				<p>Opens the tab by ID in the current drawer</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="post" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.drawer.post(value, options = {});</k-code
				>
				<p>Sends a post request to the drawer backend route</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="refresh" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.drawer.refresh(options = {});</k-code
				>
				<p>Reloads the drawer props from the backend and updates the state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="reload" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.reload(options = {});</k-code>
				<p>Reloads the drawer props and rerenders the drawer component</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="set" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.set(state);</k-code>
				<p>Overwrites the current drawer state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="state" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.state();</k-code>
				<p>Returns the current drawer state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="submit" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.drawer.submit(value, options = {});</k-code
				>
				<p>Submits the drawer with the given value</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="success" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.success(success);</k-code>
				<p>Handles a success response for the drawer</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="url" :code="false">
			<k-text>
				<k-code language="js">window.panel.drawer.url();</k-code>
				<p>
					Returns the full URL for the drawer, including the path and the URL
					query
				</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
