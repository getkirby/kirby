<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="api" :code="false">
			<k-text>
				<p>
					<code>window.panel.urls.api</code> returns the base URL for the REST
					API
				</p>
				<k-code>{{ $panel.urls.api }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="panel" :code="false">
			<k-text>
				<p>
					<code>window.panel.urls.panel</code> returns the base URL for the
					Panel
				</p>
				<k-code>{{ $panel.urls.panel }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="site" :code="false">
			<k-text>
				<p>
					<code>window.panel.urls.site</code> returns the base URL for the site
				</p>
				<k-code>{{ $panel.urls.site }}</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
