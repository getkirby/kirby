<template>
	<k-lab-examples>
		<h2 class="h4">Methods</h2>

		<k-lab-example label="close" :code="false">
			<k-text>
				<k-code language="js">window.panel.dropdown.close();</k-code>
				<p>Closes the current dialog</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="load" :code="false">
			<k-text>
				<k-code language="js">window.panel.dropdown.load(url);</k-code>
				<p>Loads the state for a dropdown by URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-code language="js">window.panel.dropdown.open(dropdown);</k-code>
				<p>Opens a dropdown either via object definition or URL</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="refresh" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.dropdown.refresh(options = {});</k-code
				>
				<p>Reloads the dropdown props from the backend and updates the state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="reload" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.dropdown.reload(options = {});</k-code
				>
				<p>Reloads the dropdown props and rerenders the dropdown component</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="set" :code="false">
			<k-text>
				<k-code language="js">window.panel.dropdown.set(state);</k-code>
				<p>Overwrites the current dropdown state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="state" :code="false">
			<k-text>
				<k-code language="js">window.panel.dropdown.state();</k-code>
				<p>Returns the current dropdown state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="url" :code="false">
			<k-text>
				<k-code language="js">window.panel.dropdown.url();</k-code>
				<p>
					Returns the full URL for the dropdown, including the path and the URL
					query
				</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
