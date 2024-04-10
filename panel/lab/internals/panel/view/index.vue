<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="component" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.component</code> returns the name of the
					component that is being rendered to display the view
				</p>
				<k-code>{{ $panel.view.component }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="breadcrumb" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.breadcrumb</code> returns an array of
					breadcrumb items that will be displayed at the top of the view
				</p>
				<k-code>{{ $panel.view.breadcrumb }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="breadcrumbLabel" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.breadcrumbLabel</code> returns the label for
					the first item in the breadcrumb
				</p>
				<k-code>{{ $panel.view.breadcrumbLabel }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="icon" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.icon</code> returns the icon for the view.
					This will also be used by the breadcrumb.
				</p>
				<k-code>{{ $panel.view.icon }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="id" :code="false">
			<k-text>
				<p><code>window.panel.view.id</code> returns the id of the view</p>
				<k-code>{{ $panel.view.id }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="isLoading" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.isLoading</code> If true, the view is
					currently being loaded/reloaded.
				</p>
				<k-code>{{ $panel.view.isLoading }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="link" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.link</code> Returns the relative base URL of
					the view
				</p>
				<k-code>{{ $panel.view.link }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="path" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.path</code> Returns the relative URL to the
					current view
				</p>
				<k-code>{{ $panel.view.path }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="props" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.props</code> Returns an object with all props
					for the current view
				</p>
				<k-code language="js">{{ $panel.view.props }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="query" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.query</code> Returns an object with the
					current URL query
				</p>
				<k-code language="js">{{ $panel.view.query }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="referrer" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.referrer</code> Returns the referrer URL if
					there's one for the request
				</p>
				<k-code>{{ $panel.view.referrer }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="search" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.search</code> Returns the selected search type
					that will be activated in the search dialog.
				</p>
				<k-code>{{ $panel.view.search }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="timestamp" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.timestamp</code> The server always returns the
					latest timestamp in requests
				</p>
				<k-code>{{ $panel.view.timestamp }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="title" :code="false">
			<k-text>
				<p>
					<code>window.panel.view.title</code> Returns the title for the current
					view
				</p>
				<k-code>{{ $panel.view.title }}</k-code>
			</k-text>
		</k-lab-example>

		<h2 class="h4">Methods</h2>

		<k-lab-example label="load" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.view.load(url, options = {});</k-code
				>
				<p>
					Loads the state of the next view without updating the current state
					yet
				</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.view.open(url, options = {});</k-code
				>
				<p>Loads and sets the state of the next view and handles routing.</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="post" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.view.post(url, options = {});</k-code
				>
				<p>Sends a post request to the view.</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="refresh" :code="false">
			<k-text>
				<k-code language="js">window.panel.view.refresh(options = {});</k-code>
				<p>
					Reloads and updates the props for the current view. This will only
					rerender the parts that have changed according to the props. The view
					component will not necessarily be rerenderd. That's the main
					difference to <code>window.panel.view.reload()</code>
				</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="reload" :code="false">
			<k-text>
				<k-code language="js">window.panel.view.reload(options = {});</k-code>
				<p>
					Reloads and rerenders the current view. If you want to refresh the
					props of the view without forcing an entire rerender process, use
					<code>window.panel.view.refresh()</code> instead.
				</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="url" :code="false">
			<k-text>
				<k-code language="js">window.panel.view.url();</k-code>
				<p>
					Returns the URL to the current view, including the path and query.
				</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
