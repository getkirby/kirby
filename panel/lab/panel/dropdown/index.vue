<template>
	<k-lab-examples>
		<h2 class="h4">Methods</h2>

		<k-lab-example label="close" :code="false">
			<k-text>
				<k-lab-code language="js">window.panel.dropdown.close();</k-lab-code>
				<p>Closes the current dialog</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="load" :code="false">
			<k-text>
				<k-lab-code language="js">window.panel.dropdown.load(url);</k-lab-code>
				<p>Loads the state for a dropdown by URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-lab-code language="js"
					>window.panel.dropdown.open(dropdown);</k-lab-code
				>
				<p>Opens a dropdown either via object definition or URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="refresh" :code="false">
			<k-text>
				<k-lab-code language="js"
					>window.panel.dropdown.refresh(options = {});</k-lab-code
				>
				<p>Reloads the dropdown props from the backend and updates the state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="reload" :code="false">
			<k-text>
				<k-lab-code language="js"
					>window.panel.dropdown.reload(options = {});</k-lab-code
				>
				<p>Reloads the dropdown props and rerenders the dropdown component</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="set" :code="false">
			<k-text>
				<k-lab-code language="js">window.panel.dropdown.set(state);</k-lab-code>
				<p>Overwrites the current dropdown state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="state" :code="false">
			<k-text>
				<k-lab-code language="js">window.panel.dropdown.state();</k-lab-code>
				<p>Returns the current dropdown state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="url" :code="false">
			<k-text>
				<k-lab-code language="js">window.panel.dropdown.url();</k-lab-code>
				<p>
					Returns the full URL for the dropdown, including the path and the URL
					query
				</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-lab-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
