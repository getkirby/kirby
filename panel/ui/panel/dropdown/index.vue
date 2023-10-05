<template>
	<k-ui-examples>
		<h2 class="h4">Methods</h2>

		<k-ui-example label="close">
			<k-text>
				<k-ui-code language="js">window.panel.dropdown.close();</k-ui-code>
				<p>Closes the current dialog</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="load">
			<k-text>
				<k-ui-code language="js">window.panel.dropdown.load(url);</k-ui-code>
				<p>Loads the state for a dropdown by URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="open">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dropdown.open(dropdown);</k-ui-code
				>
				<p>Opens a dropdown either via object definition or URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="refresh">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dropdown.refresh(options = {});</k-ui-code
				>
				<p>Reloads the dropdown props from the backend and updates the state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="reload">
			<k-text>
				<k-ui-code language="js"
					>window.panel.dropdown.reload(options = {});</k-ui-code
				>
				<p>Reloads the dropdown props and rerenders the dropdown component</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="set">
			<k-text>
				<k-ui-code language="js">window.panel.dropdown.set(state);</k-ui-code>
				<p>Overwrites the current dropdown state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="state">
			<k-text>
				<k-ui-code language="js">window.panel.dropdown.state();</k-ui-code>
				<p>Returns the current dropdown state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="url">
			<k-text>
				<k-ui-code language="js">window.panel.dropdown.url();</k-ui-code>
				<p>
					Returns the full URL for the dropdown, including the path and the URL
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
